<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\AccountingApiResource;
use App\Entity\Accounting;
use Doctrine\ORM\EntityManagerInterface;

class AccountingStateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @param AccountingApiResource $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var Accounting */
        $accounting = $this->entityManager->find(Accounting::class, $data->getId());
        $accounting->setCurrency($data->getCurrency());

        $this->entityManager->persist($accounting);
        $this->entityManager->flush();

        $owner = $this->entityManager->find($accounting->getOwnerClass(), $accounting->getOwnerId());

        return new AccountingApiResource($accounting, $owner);
    }
}
