<?php

namespace App\Library\Economy\Currency;

use App\Entity\Money;
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
     * @param MoneyContainer $money      The money to be converted
     * @param string         $toCurrency The currency to convert to
     *
     * @return Money The converted Money
     *
     * @throws CurrencyConversionException If the exchange rate is not available
     */
    public function convert(MoneyContainer $money, string $toCurrency): Money;

    /**
     * @param string $fromCurrency The currency to convert from
     * @param string $toCurrency   The currency to convert to
     *
     * @return float The rate of the conversion
     *
     * @throws CurrencyConversionException If the exchange rate is not available
     */
    public function getConversionRate(string $fromCurrency, string $toCurrency): float;

    /**
     * @param string $fromCurrency The currency to convert from
     * @param string $toCurrency   The currency to convert to
     *
     * @return \DateTimeInterface The date and time at which the rate was last updated
     *
     * @throws CurrencyConversionException If the exchange rate is not available
     */
    public function getConversionDate(string $fromCurrency, string $toCurrency): \DateTimeInterface;
}
