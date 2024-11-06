<?php

namespace App\ApiResource\Gateway;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Accounting\Accounting;
use App\Entity\Gateway\Charge as EntityCharge;
use App\Entity\Money;
use App\Gateway\ChargeType;
use App\State\Gateway\ChargeStateProvider;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Charge represents a payment item to be included in a Checkout for payment at a Gateway.
 */
#[API\ApiResource(
    shortName: 'GatewayCharge',
    stateOptions: new Options(entityClass: EntityCharge::class),
    provider: ChargeStateProvider::class
)]
#[API\Get()]
class Charge
{
    #[API\ApiProperty(writable: false, identifier: true)]
    public ?int $id = null;

    /**
     * The type represents the kind of payment that the Gateway must process.\
     * \
     * `single` is for one time payments.
     * `recurring` is for subscription-based payments.
     */
    #[Assert\NotBlank()]
    public ?ChargeType $type = ChargeType::Single;

    /**
     * A short, descriptive string for this charge item. May be displayed to the origin.
     */
    #[Assert\NotBlank()]
    public ?string $title = null;

    /**
     * Detailed information about the charge item. May be displayed to the origin.
     */
    public ?string $description = null;

    /**
     * The receiver of the successful payment.
     */
    #[Assert\NotBlank()]
    public Accounting $target;

    /**
     * The money to-be-paid at the gateway.
     * 
     * It is money before gateway fees and taxes, not accountable.
     */
    #[Assert\NotBlank()]
    public Money $money;
}
