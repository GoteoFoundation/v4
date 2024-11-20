<?php

namespace App\ApiResource\Accounting;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\Entity\Accounting as Entity;
use App\Entity\Interface\AccountingOwnerInterface;
use App\Entity\Money;
use App\State\Accounting\AccountingStateProcessor;
use App\State\Accounting\AccountingStateProvider;

/**
 * Accountings represent payment services used to perform the necessary payments for Transactions between Accounts.\
 * \
 * For each Accounting there is an internal implementation that handles the creation and validation of Transactions between Accounts.
 * These implementations make use of external or internal services to gather the funds that are inside a Transaction,
 * perform corroboration of funds and store the Transactions into the system.
 */
#[API\ApiResource(
    stateOptions: new Options(entityClass: Entity\Accounting::class),
    provider: AccountingStateProvider::class,
    processor: AccountingStateProcessor::class,
)]
#[API\GetCollection()]
#[API\Get()]
#[API\Patch(security: 'is_granted("ACCOUNTING_EDIT", object)')]
class Accounting
{
    public ?int $id = null;

    public ?string $currency = null;

    public ?AccountingOwnerInterface $owner = null;

    public ?Money $balance = null;
}
