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
     * Generates an income statement for User-received Transactions.
     */
    public function processTransaction(
        Transaction $transaction,
        PostPersistEventArgs $event,
    ) {
        if (!$transaction->getTarget()->getOwner() instanceof User) {
            return;
        }

        $income = $this->wallet->save($transaction);

        if ($income->getId() !== null) {
            return;
        }

        $event->getObjectManager()->persist($income);
        $event->getObjectManager()->flush();
    }
}
