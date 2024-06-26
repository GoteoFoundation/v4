<?php

namespace App\Library\Economy\Currency;

use App\Entity\Money as EntityMoney;
use Brick\Money\Exception\CurrencyConversionException;
use Brick\Money\MoneyContainer;

interface ExchangeInterface
{
    /**
     * @return string A short, unique, descriptive string of your exchange
     */
    public function getName(): string;

    /**
     * @return int The heavier the weight, the less priority it will have
     */
    public function getWeight(): int;

    /**
     * @param MoneyContainer $money    The money to be converted
     * @param string         $currency The currency to convert to
     *
     * @return EntityMoney The converted Money
     *
     * @throws CurrencyConversionException If the exchange rate is not available
     */
    public function getConversion(MoneyContainer $money, string $currency): EntityMoney;

    /**
     * @param string $source The currency to convert from
     * @param string $target The currency to convert to
     *
     * @return float The rate of the conversion
     *
     * @throws CurrencyConversionException If the exchange rate is not available
     */
    public function getConversionRate(string $source, string $target): float;

    /**
     * @param string $source The currency to convert from
     * @param string $target The currency to convert to
     *
     * @return \DateTimeInterface The date and time at which the rate was last updated
     *
     * @throws CurrencyConversionException If the exchange rate is not available
     */
    public function getConversionDate(string $source, string $target): \DateTimeInterface;
}
