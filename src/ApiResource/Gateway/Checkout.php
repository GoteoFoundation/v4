<?php

namespace App\ApiResource\Gateway;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Accounting\Accounting;
use App\Entity\Gateway as Entity;
use App\Gateway\CheckoutStatus;
use App\Gateway\Link;
use App\Gateway\Tracking;
use App\State\Gateway\CheckoutStateProcessor;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A GatewayCheckout represents a payment session with a Gateway.
 */
#[API\ApiResource(
    shortName: 'GatewayCheckout',
    stateOptions: new Options(entityClass: Entity\Checkout::class),
    processor: CheckoutStateProcessor::class
)]
#[API\GetCollection()]
#[API\Post()]
#[API\Get()]
class Checkout
{
    #[API\ApiProperty(writable: false, identifier: true)]
    public ?int $id = null;

    /**
     * The desired Gateway to checkout with.
     */
    #[Assert\NotBlank()]
    public Gateway $gateway;

    /**
     * The Accounting paying for the charges.
     */
    #[Assert\NotBlank()]
    public Accounting $origin;

    /**
     * A list of the payment items to be charged to the origin.
     *
     * @var Charge[]
     */
    #[API\ApiProperty(readableLink: true, writableLink: true)]
    #[Assert\NotBlank()]
    #[Assert\Count(min: 1)]
    public array $charges = [];

    /**
     * The status of this Checkout, as confirmed by the Gateway.
     */
    #[API\ApiProperty(writable: false)]
    public CheckoutStatus $status = CheckoutStatus::Pending;

    /**
     * A list of related hyperlinks, as provided by the Gateway.
     *
     * @var Link[]
     */
    #[API\ApiProperty(writable: false)]
    public array $links = [];

    /**
     * A list of related tracking codes and numbers, as provided by the Gateway.
     *
     * @var Tracking[]
     */
    #[API\ApiProperty(writable: false)]
    public array $trackings = [];
}
