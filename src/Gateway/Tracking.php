<?php

namespace App\Gateway;

use Symfony\Component\Validator\Constraints as Assert;

class Tracking
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
