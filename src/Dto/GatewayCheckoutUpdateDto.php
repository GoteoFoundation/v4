<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class GatewayCheckoutUpdateDto
{
    /**
     * An external identifier provided by the Gateway for the payment.
     */
    #[Assert\NotBlank()]
    public readonly string $gatewayReference;

    public function __construct(string $gatewayReference)
    {
        $this->gatewayReference = $gatewayReference;
    }
}
