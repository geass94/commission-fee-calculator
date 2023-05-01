<?php

namespace App\Service\Impl;

use App\Country;
use App\Service\TransactionProcessor;
use App\Transaction;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Exception;
use GuzzleHttp\Client;

class TransactionProcessorImpl implements TransactionProcessor
{
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client(['verify' => false]);
    }

    public function readTransactions(mixed $input): array
    {

        foreach (file($input) as $line) {
            $data = json_decode(trim($line), true);
            $transactions[] = new Transaction(
                $data['bin'],
                $data['amount'],
                $data['currency']
            );
        }
        return $transactions;
    }

    /**
     * @param string $currency
     * @return Country
     * @throws \Exception
     */
    public function getBinCountry(string $bin): Country
    {
        $req = $this->httpClient->get("https://lookup.binlist.net/{$bin}");
        if ($req->getStatusCode() !== 200) throw new \Exception('API call failed');
        $res = json_decode($req->getBody()->getContents(), true);
        return new Country($res['country']['name'], $res['country']['currency'], $res['country']['alpha2']);
    }

    /**
     * @param string $currency Currency that we want to get rate based on base currency
     * @param string $baseCurrency optional
     * @return float Rate of currency based on base currency
     * @throws \Exception
     */
    public function getRate(string $currency, string $baseCurrency = 'USD'): float
    {
        $req = $this->httpClient->get("https://api.apilayer.com/exchangerates_data/latest?base=$baseCurrency", [
            'headers' => [
                'Content-Type' => 'application/json',
                'apikey' => 'mZ2vhmo0c2LBCE5wFRG1O6eI5HT1WOgs'
            ]
        ]);
        if ($req->getStatusCode() !== 200) throw new \Exception('Failed to retrieve rates');
        $res = json_decode($req->getBody()->getContents(), true);
        return $res['rates'][strtoupper($currency)];
    }


    /**
     * @throws \Exception
     * @return BigDecimal Commissioned amount
     */
    public function getCommissionedAmount(Transaction $transaction): BigDecimal
    {
//        Get currency rate by Transaction`s currency
        $rate = $this->getRate($transaction->getCurrency());

        $amount = $transaction->getAmount();

//        If base and comparing currencies are different we have to apply currency rate
        if ($transaction->getCurrency() !== 'EUR' && $rate > 0) $amount = $amount->dividedBy($rate, null, RoundingMode::HALF_UP);

//        Apply commission fee based on Transaction`s bin number
        $amount = $amount->multipliedBy($transaction->getCommissionRate());

//        Round down amount to two decimals
        return BigDecimal::of(round($amount->toFloat(), 2, RoundingMode::HALF_UP));

    }

}