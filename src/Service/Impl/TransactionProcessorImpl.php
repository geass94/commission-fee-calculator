<?php

namespace App\Service\Impl;

use App\Country;
use App\Service\BinProviderService;
use App\Service\ExchangeRateService;
use App\Service\TransactionProcessor;
use App\Transaction;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Exception;
use GuzzleHttp\Client;

class TransactionProcessorImpl implements TransactionProcessor
{
    public function __construct(private $binProvider = new BinProviderService(), private $rateProvider = new ExchangeRateService())
    {
    }

    public function readTransactions(mixed $input): array
    {
        $transactions = [];
        if (!file_exists($input)) throw new Exception("File at location '$input' does not exist!", 404);
        foreach (file($input) as $line) {
            $data = json_decode(trim($line), true);
            if (sizeof($data) !== 3) throw new Exception('Your file has broken Transaction data.', 422);

            $transactions[] = new Transaction(
                $data['bin'],
                $data['amount'],
                $data['currency']
            );
        }
        return $transactions;
    }


    public function isFromEU(Transaction $transaction) : bool
    {
        $country = $this->binProvider->getBinCountry($transaction->getBin());
        return in_array($country->countryCode, Transaction::EU_COUNTRY_CODES);
    }

    /**
     * @return BigDecimal Commissioned amount
     * @throws \Exception
     */
    public function getCommissionedAmount(Transaction $transaction): BigDecimal
    {
        $exchangeRate = $this->rateProvider->getRate($transaction->getCurrency());
        $amount = $transaction->getAmount();
        $isFromEU = $this->isFromEU($transaction);
        $commissionMultiplier = $transaction->getCommissionMultiplier($isFromEU);

        if ($transaction->getCurrency() !== 'EUR' && $exchangeRate > 0) $amount = $amount->dividedBy($exchangeRate, null, RoundingMode::HALF_UP);

        $amount = $amount->multipliedBy($commissionMultiplier);

        return BigDecimal::of(round($amount->toFloat(), 2, RoundingMode::HALF_UP));

    }

}