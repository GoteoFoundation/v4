<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embeddable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a fixed monetary value.
 */
#[Embeddable]
class Money
{
    /**
     * The amount of the currency.\
     * Expressed as the minor possible unit, e.g: cents, pennies, etc.
     */
    #[Assert\NotBlank()]
    #[Assert\Positive()]
    #[ORM\Column(type: 'integer', nullable: true)]
    public readonly int $amount;

    /**
     * 3-letter ISO 4217 currency code.
     */
    #[Assert\NotBlank()]
    #[Assert\Currency()]
    #[ORM\Column(type: 'string', nullable: true)]
    public readonly string $currency;

    public function __construct(
        int $amount,
        string $currency
    )
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }
}
