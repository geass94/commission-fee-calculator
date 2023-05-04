<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\Impl\TransactionProcessorImpl;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CommissionFeeController extends AbstractController
{
    public function __construct(private TransactionProcessorImpl $processor)
    {
    }

    /**
     * @throws \Exception
     */
    #[Route('/api/commission/fee', name: 'app_commission_fee')]
    public function index(): JsonResponse
    {
        try {
            $transactions = $this->processor->readTransactions('/var/www/input.txt');
        } catch (\Throwable $exception) {
            return $this->json([
                'error' => true,
                'message' => $exception->getMessage()
            ], $exception->getCode());
        }
        $fees = [];
        try {
            foreach ($transactions as $transaction) {
                $fees[] = $this->processor->getCommissionedAmount($transaction);
            }
        } catch (\Exception $exception) {
            return $this->json([
                'error' => true,
                'message' => $exception->getMessage()
            ], $exception->getCode());
        }
        return $this->json($fees);
    }
}
