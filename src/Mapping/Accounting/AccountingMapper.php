<?php

namespace App\Mapping\Accounting;

use App\ApiResource\Accounting as Resource;
use App\Entity\Accounting as Entity;
use App\Entity\Interface\AccountingOwnerInterface;
use App\Entity\Money;
use App\Entity\User;
use App\Gateway\Wallet\WalletService;
use App\Library\Economy\MoneyService;
use App\Repository\Accounting\AccountingRepository;
use App\Repository\Accounting\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;

class AccountingMapper
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AccountingRepository $accountingRepository,
        private TransactionRepository $transactionRepository,
        private WalletService $wallet,
        private MoneyService $money,
    ) {}

    public function toResource(Entity\Accounting $entity): Resource\Accounting
    {
        $owner = $entity->getOwner();

        $resource = new Resource\Accounting();
        $resource->id = $entity->getId();
        $resource->currency = $entity->getCurrency();
        $resource->owner = $owner;
        $resource->balance = $this->getBalance($owner);

        return $resource;
    }

    public function toEntity(Resource\Accounting $resource): Entity\Accounting
    {
        $entity = new Entity\Accounting();

        if ($resource->id !== null) {
            $entity = $this->accountingRepository->find($resource->id);
        }

        $entity->setCurrency($resource->currency);
        $entity->setOwner($resource->owner);

        return $entity;
    }

    private function getBalance(AccountingOwnerInterface $owner): Money
    {
        if ($owner instanceof User) {
            return $this->wallet->getBalance($owner->getAccounting());
        }

        $accounting = $owner->getAccounting();

        $balance = new Money(0, $accounting->getCurrency());
        $transactions = $this->transactionRepository->findByAccounting($accounting);

        foreach ($transactions as $transaction) {
            if ($transaction->getTarget() === $accounting) {
                $balance = $this->money->add($transaction->getMoney(), $balance);
            }

            if ($transaction->getOrigin() === $accounting) {
                $balance = $this->money->substract($transaction->getMoney(), $balance);
            }
        }

        return $balance;
    }
}
