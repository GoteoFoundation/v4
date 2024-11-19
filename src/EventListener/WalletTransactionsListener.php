<?php

namespace App\EventListener;

use App\Entity\Accounting\Transaction;
use App\Entity\User;
use App\Gateway\Wallet\WalletService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(
    event: Events::postPersist,
    method: 'processTransaction',
    entity: Transaction::class
)]
final class WalletTransactionsListener
{
    public function __construct(
        private WalletService $wallet,
    ) {}

    /**
     * Generates a WalletStatement for User-received Transactions.
     */
    public function processTransaction(
        Transaction $transaction,
        PostPersistEventArgs $event,
    ) {
        if (!$transaction->getTarget()->getOwner() instanceof User) {
            return;
        }

        $statement = $this->wallet->save($transaction);

        if ($statement->getId() !== null) {
            return;
        }

        $event->getObjectManager()->persist($statement);
        $event->getObjectManager()->flush();
    }
}
