<?php

namespace App\Library\Economy\Currency;

use App\Library\Economy\AbstractMonetizable;
use Brick\Money\Exception\CurrencyConversionException;

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
     * @param AbstractMonetizable $money The money to be converted
     * @param string $currency The currency to convert to
     * @return AbstractMonetizable The converted Money
     * @throws CurrencyConversionException If the exchange rate is not available
     */
    public function getConversion(AbstractMonetizable $money, string $currency): AbstractMonetizable;

    /**
     * @param string $source The currency to convert from
     * @param string $target The currency to convert to
     * @return float The rate of the conversion
     * @throws CurrencyConversionException If the exchange rate is not available
     */
    public function getConversionRate(string $source, string $target): float;
}
