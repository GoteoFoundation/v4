<?php

namespace App\ApiResource\Gateway;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Accounting\AccountingApiResource;
use App\Entity\Gateway\Checkout;
use App\Gateway\CheckoutStatus;
use App\Gateway\Link;
use App\Gateway\Tracking;
use App\Mapping\Transformer\GatewayNameMapTransformer;
use App\State\ApiResourceStateProvider;
use App\State\Gateway\CheckoutStateProcessor;
use AutoMapper\Attribute\MapFrom;
use AutoMapper\Attribute\MapTo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A GatewayCheckout represents a payment session with a Gateway.
 */
#[API\ApiResource(
    shortName: 'GatewayCheckout',
    stateOptions: new Options(entityClass: Checkout::class),
    provider: ApiResourceStateProvider::class,
    processor: CheckoutStateProcessor::class,
)]
#[API\GetCollection()]
#[API\Post()]
#[API\Get()]
class CheckoutApiResource
{
    #[API\ApiProperty(writable: false, identifier: true)]
    public int $id;

    /**
     * The desired Gateway to checkout with.
     */
    #[Assert\NotBlank()]
    #[MapFrom(property: 'gatewayName', transformer: GatewayNameMapTransformer::class)]
    #[MapTo(property: 'gatewayName', transformer: 'source.gateway.name')]
    public GatewayApiResource $gateway;

    /**
     * The Accounting paying for the charges.
     */
    #[Assert\NotBlank()]
    public AccountingApiResource $origin;

    /**
     * A list of the payment items to be charged to the origin.
     *
     * @var ChargeApiResource[]
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
    #[MapFrom(transformer: [self::class, 'parseTrackings'])]
    public array $trackings = [];

    public static function parseTrackings(array $values)
    {
        return \array_map(function ($value) {
            $tracking = new Tracking();
            $tracking->title = $value['title'];
            $tracking->value = $value['value'];

            return $tracking;
        }, $values);
    }
}
