<?php

namespace App\Library\Economy;

use Brick\Money\AbstractMoney;

/**
 * Monetizables are able to be operated as basic Moneys.
 */
class Monetizable extends AbstractMonetizable
{
    /**
     * An amount of currency, expressed in the minor unit (cents, pennies, etc).
     */
    protected int $amount;

    /**
     * 3-letter ISO 4217 currency code
     */
    protected string $currency;

    /**
     * @return string An amount of currency, expressed in the minor unit (cents, pennies, etc).
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param string $amount An amount of currency, expressed in the minor unit (cents, pennies, etc).
     * @return static
     */
    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string 3-letter ISO 4217 currency code.
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string 3-letter ISO 4217 currency code.
     * @return static
     */
    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public static function ofBrickMoney(AbstractMoney $money): Monetizable
    {
        $result = new Monetizable();

        $result->setCurrency($money->getCurrency());
        $result->setAmount(
            $money
                ->getAmount()
                ->toBigDecimal()
                ->withPointMovedRight($money->getCurrency()->getDefaultFractionDigits())
                ->toInt()
        );

        return $result;
    }

    public function plus(Monetizable $money): Monetizable
    {
        return self::ofBrickMoney(
            $this->toBrickMoney()->plus($money->toBrickMoney())
        );
    }

    public function minus(Monetizable $money): Monetizable
    {
        return self::ofBrickMoney(
            $this->toBrickMoney()->minus($money->toBrickMoney())
        );
    }
}
