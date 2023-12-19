<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Transaction;
use App\Service\AccountingService;
use Doctrine\ORM\EntityManagerInterface;

class TransactionStateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AccountingService $accountingService
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var Transaction */
        $transaction = $data;

        if (
            $transaction->getOrigin()->getAccounting()->getId() ===
            $transaction->getTarget()->getAccounting()->getId()
        ) {
            throw new \Exception("The target Accounting cannot be the same as the origin Accounting");
        }

        $transaction = $this->accountingService->spendTransaction($transaction);
        $transaction = $this->accountingService->storeTransaction($transaction);

        var_dump($transaction);
        exit;

        return $transaction;
    }
}
