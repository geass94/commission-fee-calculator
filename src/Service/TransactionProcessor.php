<?php

namespace App\Service;

use App\Country;
use App\Transaction;
use Brick\Math\BigDecimal;

interface TransactionProcessor
{
    public function readTransactions(mixed $input): array;
    public function getBinCountry(string $bin): Country;
    public function getRate(string $currency, string $baseCurrency = 'USD'): float;
    public function getCommissionedAmount(Transaction $transaction): BigDecimal;
}