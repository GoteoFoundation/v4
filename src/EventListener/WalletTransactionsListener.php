<?php

namespace App\EventListener;

use App\Entity\AccountingTransaction;
use App\Entity\User;
use App\Library\Economy\Payment\WalletGatewayService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(
    event: Events::postPersist,
    method: 'processTransaction',
    entity: AccountingTransaction::class
)]
final class WalletTransactionsListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WalletGatewayService $wallet
    ) {}

    /**
     * Generates a WalletStatement for User-received Transactions.
     */
    public function processTransaction(
        AccountingTransaction $transaction,
        PostPersistEventArgs $event,
    ) {
        $target = $transaction->getTarget();

        if ($target->getOwnerClass() === User::class) {
            $this->wallet->save($transaction);
        }
    }
}
