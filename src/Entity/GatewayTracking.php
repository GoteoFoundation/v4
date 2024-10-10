<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class GatewayTracking
{
    /**
     * A descriptive title for the tracking number.
     */
    #[Assert\NotBlank()]
    public string $title;

    /**
     * The tracking number given by the Gateway.
     */
    #[Assert\NotBlank()]
    public string $value;
}
