<?php

namespace App\State\Accounting;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Accounting as ApiResource;
use App\Entity\Accounting as Entity;
use Doctrine\ORM\EntityManagerInterface;

class AccountingStateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @param ApiResource\Accounting $data
     *
     * @return ApiResource\Accounting
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var Accounting */
        $accounting = $this->entityManager->find(Entity\Accounting::class, $data->id);
        $accounting->setCurrency($data->currency);

        $this->entityManager->persist($accounting);
        $this->entityManager->flush();

        $owner = $this->entityManager->find($accounting->getOwnerClass(), $accounting->getOwnerId());

        return new ApiResource\Accounting($accounting, $owner);
    }
}
