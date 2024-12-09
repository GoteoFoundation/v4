<?php

namespace App\ApiResource\Accounting;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\Entity\Accounting\Accounting;
use App\Entity\Money;
use App\State\Accounting\AccountingStateProcessor;
use App\State\Accounting\AccountingStateProvider;

/**
 * v4 features an advanced economy model under the hood.
 * Accountings are implemented as a common interface for issuing and receiving Transactions,
 * which allows different resources to have money-capabalities.
 * \
 * \
 * Many different actions can trigger changes in Accountings, such as GatewayCheckouts being successfully charged.
 */
#[API\ApiResource(
    shortName: 'Accounting',
    stateOptions: new Options(entityClass: Accounting::class),
    provider: AccountingStateProvider::class,
    processor: AccountingStateProcessor::class,
)]
#[API\GetCollection()]
#[API\Get()]
#[API\Patch(security: 'is_granted("ACCOUNTING_EDIT", object)')]
class AccountingApiResource
{
    public int $id;

    /**
     * The preferred currency for monetary operations.\
     * 3-letter ISO 4217 currency code.
     */
    public string $currency;

    /**
     * The resource owning this Accounting.\.
     */
    public mixed $owner;

    /**
     * The money currently held by the Accounting.
     */
    public Money $balance;
}
