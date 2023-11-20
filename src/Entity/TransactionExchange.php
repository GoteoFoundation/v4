<?php

namespace App\Entity;

use App\Library\Economy\Monetizable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class TransactionExchange extends Monetizable
{
    /**
     * The amount of the asked-for currency.\
     * Expressed in the minor unit of the currency (cents, pennies, etc)
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    protected int $amount;

    /**
     * The asked-for currency.\
     * 3-letter ISO 4217 currency code.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    protected string $currency;

    /**
     * The rate used in the conversion.
     */
    #[ORM\Column(type: 'float', nullable: true)]
    public readonly float $rate;

    /**
     * The source of the conversion rate.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    public readonly string $rateProvider;

    public function __construct(
        int $amount,
        string $currency,
        float $rate,
        string $rateProvider
    )
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->rate = $rate;
        $this->rateProvider = $rateProvider;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
