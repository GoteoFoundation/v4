<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\Entity\Accounting;
use App\Entity\Interface\AccountingOwnerInterface;
use App\Entity\Interface\ApiResource;
use App\State\AccountingStateProcessor;
use App\State\AccountingStateProvider;

/**
 * Accountings represent payment services used to perform the necessary payments for Transactions between Accounts.\
 * \
 * For each Accounting there is an internal implementation that handles the creation and validation of Transactions between Accounts.
 * These implementations make use of external or internal services to gather the funds that are inside a Transaction,
 * perform corroboration of funds and store the Transactions into the system.
 */
#[API\ApiResource(
    shortName: 'Accounting',
    stateOptions: new Options(entityClass: Accounting::class)
)]
#[API\GetCollection(provider: AccountingStateProvider::class)]
#[API\Get(provider: AccountingStateProvider::class)]
#[API\Patch(
    processor: AccountingStateProcessor::class,
    security: 'is_granted("ACCOUNTING_EDIT", object)'
)]
class AccountingApiResource
{
    public function __construct(
        private readonly Accounting $accounting,
        private readonly AccountingOwnerInterface $owner
    ) {}

    public function getId(): ?int
    {
        return $this->accounting->getId();
    }

    public function getCurrency(): ?string
    {
        return $this->accounting->getCurrency();
    }

    public function setCurrency(string $currency): static
    {
        $this->accounting->setCurrency($currency);

        return $this;
    }

    public function getOwner(): ApiResource
    {
        return $this->owner;
    }
}
