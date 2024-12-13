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
     * Compares if one Money is of less value than other.
     *
     * @return bool `true` if `$money` is less than `$than`
     */
    public function isLess(Money $money, Money $than): bool
    {
        $money = $this->convert($money, $than->currency);

        return $money->isLessThan(self::toBrick($than));
    }

    /**
     * Compares if one Money is of greater or equal value than other.
     *
     * @return bool `true` if `$money` is more than or same as `$than`
     */
    public function isMoreOrSame(Money $money, Money $than): bool
    {
        $money = $this->convert($money, $than->currency);

        return $money->isGreaterThanOrEqualTo(self::toBrick($than));
    }

    private function convert(Money $money, string $toCurrency): Brick\Money
    {
        $fromCurrency = $money->currency;
        if ($fromCurrency === $toCurrency) {
            return self::toBrick($money);
        }

        $exchange = $this->exchangeLocator->get($fromCurrency, $toCurrency);

        return self::toBrick($exchange->convert($money, $toCurrency));
    }
}
