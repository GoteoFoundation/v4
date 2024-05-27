<?php

namespace App\Dto;

use App\Entity\GatewayCheckoutStatus;
use Symfony\Component\Validator\Constraints as Assert;

final class GatewayCheckoutUpdateDto
{
    /**
     * The status of the checkout with the Gateway.
     */
    #[Assert\NotBlank()]
    public GatewayCheckoutStatus $status;

    /**
     * An external identifier provided by the Gateway for the payment.
     */
    #[Assert\NotBlank()]
    public string $gatewayReference;

    public function __construct(
        GatewayCheckoutStatus $status,
        string $gatewayReference
    ) {
        $this->status = $status;
        $this->gatewayReference = $gatewayReference;
    }
}
