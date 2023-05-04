<?php
declare(strict_types=1);

namespace App\Service\Impl;

use App\DTO\Transaction;
use App\Service\TransactionProcessor;
use Brick\Math\BigDecimal;

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