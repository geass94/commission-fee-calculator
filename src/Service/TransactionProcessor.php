<?php

namespace App\Service;

use App\Country;
use App\Transaction;
use Brick\Math\BigDecimal;

interface TransactionProcessor
{
    public function readTransactions(mixed $input): array;
    public function isFromEU(Transaction $transaction): bool;
    public function getCommissionedAmount(Transaction $transaction): BigDecimal;
}