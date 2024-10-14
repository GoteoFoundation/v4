<?php

namespace App\EventListener;

use App\Entity\AccountingTransaction;
use App\Entity\User;
use App\Entity\WalletStatement;
use App\Entity\WalletStatementDirection;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'processTransaction', entity: AccountingTransaction::class)]
final class WalletTransactionsListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * Generates a WalletStatement for User-received Transactions.
     */
    public function processTransaction(
        AccountingTransaction $transaction,
        PostPersistEventArgs $event,
    ) {
        $target = $transaction->getTarget();
        if ($target->getOwnerClass() !== User::class) {
            return;
        }

        $statement = new WalletStatement();
        $statement->setTransaction($transaction);
        $statement->setDirection(WalletStatementDirection::Incoming);

        $this->entityManager->persist($statement);
        $this->entityManager->flush();
    }
}
