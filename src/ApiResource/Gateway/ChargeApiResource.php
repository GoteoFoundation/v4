<?php

namespace App\ApiResource\Gateway;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Accounting\AccountingApiResource;
use App\Entity\Gateway\Charge;
use App\Entity\Money;
use App\Gateway\ChargeType;
use App\State\ApiResourceStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Charge represents a payment item to be included in a Checkout for payment at a Gateway.
 */
#[API\ApiResource(
    shortName: 'GatewayCharge',
    stateOptions: new Options(entityClass: Charge::class),
    provider: ApiResourceStateProvider::class
)]
#[API\Get()]
class ChargeApiResource
{
    #[API\ApiProperty(writable: false, identifier: true)]
    public ?int $id = null;

    /**
     * How this item should be processed by the Gateway.\
     * \
     * `single` is for one time payments.\
     * `recurring` is for payments repeated over time.
     */
    #[Assert\NotBlank()]
    public ChargeType $type = ChargeType::Single;

    /**
     * A short, descriptive string for this charge item.\
     * May be displayed to the payer.
     */
    #[Assert\NotBlank()]
    public string $title;

    /**
     * Detailed information about the charge item.\
     * May be displayed to the payer.
     */
    public ?string $description = null;

    /**
     * The Accounting receiving the money after a successful payment.
     */
    #[Assert\NotBlank()]
    public AccountingApiResource $target;

    /**
     * The money to-be-paid for this item at the Gateway.
     *
     * It is money before fees and taxes, not accountable.
     */
    #[Assert\NotBlank()]
    public Money $money;
}
