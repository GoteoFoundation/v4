<?php

namespace App\EventListener;

use App\Entity\Accounting;
use App\Entity\Interface\AccountingOwnerInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(
    event: Events::postPersist,
)]
final class AccountingOwnerListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * Generates a WalletStatement for User-received Transactions.
     */
    public function postPersist(
        PostPersistEventArgs $event,
    ) {
        $entity = $event->getObject();

        if (!$entity instanceof AccountingOwnerInterface) {
            return;
        }

        $accounting = $entity->getAccounting();
        if ($accounting === null || $accounting->getId() === null) {
            $accounting = new Accounting();
            $accounting->setOwner($entity);

            $entity->setAccounting($accounting);

            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        }
    }
}
