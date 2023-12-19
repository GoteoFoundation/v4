<?php

namespace App\Library\Economy;

use ApiPlatform\Metadata as API;
use Brick\Money\AbstractMoney;
use Brick\Money\Money;

/**
 * Monetizables are able to be operated as basic Moneys.
 */
class Monetizable
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

    public function hasCurrencyOf(Monetizable $money): bool
    {
        return $this->getCurrency() === $money->getCurrency();
    }

    protected static function getBrickMoneyMinorAmount(AbstractMoney $money): int
    {
        return $money
            ->getAmount()
            ->toBigDecimal()
            ->withPointMovedRight($money->getCurrency()->getDefaultFractionDigits())
            ->toInt();
    }

    protected function toBrickMoney(): Money
    {
        return Money::ofMinor(
            $this->getAmount(),
            $this->getCurrency()
        );
    }

    /**
     * Mutably update this instance to have the same currency and amount of a `Brick\Money\AbstractMoney` instance
     * @param AbstractMoney $money
     */
    public function fromBrickMoney(AbstractMoney $money): static
    {
        $this->setCurrency($money->getCurrency());
        $this->setAmount(self::getBrickMoneyMinorAmount($money));

        return $this;
    }

    /**
     * Create a new instance with the same currency and amount of a `Brick\Money\AbstractMoney` instance
     * @param AbstractMoney $money
     */
    public static function ofBrickMoney(AbstractMoney $money): Monetizable
    {
        $result = new Monetizable();

        $result->setCurrency($money->getCurrency());
        $result->setAmount(self::getBrickMoneyMinorAmount($money));

        return $result;
    }

    #[API\ApiProperty(readable: false)]
    public function isZero(): bool
    {
        return $this->toBrickMoney()->isZero();
    }

    public function isLessThan(Monetizable $money): bool
    {
        return $this
            ->toBrickMoney()
            ->isLessThan($money->toBrickMoney());
    }

    public function isLessThanOrEqualTo(Monetizable $money): bool
    {
        return $this
            ->toBrickMoney()
            ->isLessThanOrEqualTo($money->toBrickMoney());
    }

    public function isGreaterThan(Monetizable $money): bool
    {
        return $this
            ->toBrickMoney()
            ->isGreaterThan($money->toBrickMoney());
    }

    public function isGreaterThanOrEqualTo(Monetizable $money): bool
    {
        return $this
            ->toBrickMoney()
            ->isGreaterThanOrEqualTo($money->toBrickMoney());
    }

    public function plus(Monetizable $money): static
    {
        return $this->fromBrickMoney(
            $this
                ->toBrickMoney()
                ->plus($money->toBrickMoney())
        );
    }

    public function minus(Monetizable $money): static
    {
        return $this->fromBrickMoney(
            $this
                ->toBrickMoney()
                ->minus($money->toBrickMoney())
        );
    }
}
