<?php

namespace App\Library\Economy;

use App\Entity\Money;
use App\Library\Economy\Currency\ExchangeLocator;
use Brick\Money as Brick;

class MoneyService
{
    public function __construct(
        private ExchangeLocator $exchangeLocator,
    ) {}

    public static function toMoney(Brick\Money $brick): Money
    {
        return new Money(
            $brick->getMinorAmount()->toInt(),
            $brick->getCurrency()->getCurrencyCode()
        );
    }

    public static function toBrick(Money $money): Brick\Money
    {
        return Brick\Money::ofMinor($money->amount, $money->currency);
    }

    /**
     * Adds `a` to `b`.
     */
    public function add(Money $a, Money $b): Money
    {
        $a = $this->convert($a, $b->currency);
        $ab = self::toBrick($b)->plus($a);

        return self::toMoney($ab);
    }

    /**
     * Substracts `a` from `b`.
     */
    public function substract(Money $a, Money $b): Money
    {
        $a = $this->convert($a, $b->currency);
        $ab = self::toBrick($b)->minus($a);

        return self::toMoney($ab);
    }

    /**
     * Compares `a` to `b`.
     *
     * @return bool `true` if `a` is less than `b`
     */
    public function isLessThan(Money $a, Money $b): bool
    {
        $a = $this->convert($a, $b->currency);

        return $a->isLessThan(self::toBrick($b));
    }

    /**
     * Compaers `a` to `b`.
     *
     * @return bool `true` if `a` is more than `b`
     */
    public function isGreaterThan(Money $a, Money $b): bool
    {
        $a = $this->convert($a, $b->currency);

        return $a->isGreaterThan(self::toBrick($b));
    }

    private function convert(Money $money, string $toCurrency): Brick\Money
    {
        $fromCurrency = $money->currency;
        if ($fromCurrency === $toCurrency) {
            return $money;
        }

        $exchange = $this->exchangeLocator->getExchangeFor($fromCurrency, $toCurrency);

        return self::toBrick($exchange->convert(self::toBrick($money), $toCurrency));
    }
}
