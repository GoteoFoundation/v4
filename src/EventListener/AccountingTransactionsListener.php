<?php

namespace App\EventListener;

use App\Entity\AccountingTransaction;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: AccountingTransaction::class)]
final class AccountingTransactionsListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function postPersist(
        AccountingTransaction $transaction,
        PostPersistEventArgs $event,
    ) {
        $origin = $transaction->getOrigin();
        $origin->addTransactionsIssued($transaction);

        $this->entityManager->persist($origin);

        $target = $transaction->getTarget();
        $target->addTransactionsReceived($transaction);

        $this->entityManager->persist($target);

        $this->entityManager->flush();
    }
}
