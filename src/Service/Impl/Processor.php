<?php

namespace App\Service\Impl;

use App\Country;
use App\Service\TransactionProcessor;
use App\Transaction;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use GuzzleHttp\Client;

class Processor implements TransactionProcessor
{

    public function readTransactions(mixed $input): array
    {
        // TODO: Implement readTransactions() method.
    }

    public function isFromEU(Transaction $transaction): bool
    {
        // TODO: Implement isFromEU() method.
    }

    public function getCommissionedAmount(Transaction $transaction): BigDecimal
    {
        // TODO: Implement getCommissionedAmount() method.
    }
}