<?php

namespace App\Controller;

use App\Service\Impl\TransactionProcessorImpl;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CommissionFeeController extends AbstractController
{
    /**
     * @throws \Exception
     */
    #[Route('/commission/fee', name: 'app_commission_fee')]
    public function index(TransactionProcessorImpl $processor): JsonResponse
    {
        $transactions = $processor->readTransactions(('C:\Users\okori\OneDrive\Desktop\task\commission-calculator\input.txt'));
        $fees = [];
        foreach ($transactions as $transaction) {
            try {
                $fees[] = $processor->getCommissionedAmount($transaction);
            } catch (\Exception $exception) {
//                Send API request for example to Sentry.io with exception data and also log locally
            }
        }
        return $this->json($fees);
    }
}
