<?php

namespace App\Library\Economy\Currency;

use Brick\Money\Exception\CurrencyConversionException;

class ExchangeLocator
{
    /**
     * @var array<string, ExchangeInterface>
     */
    private array $exchanges;

    /**
     * @param iterable<int, ExchangeInterface> $exchanges
     */
    public function __construct(iterable $exchanges)
    {
        /** @var ExchangeInterface[] */
        $exchanges = \iterator_to_array($exchanges);

        /*
         * @param ExchangeInterface $a
         * @param ExchangeInterface $b
         */
        usort($exchanges, function ($a, $b) {
            return $a->getWeight() < $b->getWeight();
        });

        foreach ($exchanges as $exchange) {
            $this->exchanges[$exchange->getName()] = $exchange;
        }
    }

    /**
     * @return array<string, ExchangeInterface>
     */
    public function getAll(): array
    {
        return $this->exchanges;
    }

    /**
     * @param string $name Name of the Exchange interface implementation
     *
     * @throws \Exception When the $name does not match to that of an implemented Exchange
     */
    public function getByName(string $name): ExchangeInterface
    {
        if (!\array_key_exists($name, $this->exchanges)) {
            throw new \Exception("No such Exchange with the name '$name'");
        }

        return $this->exchanges[$name];
    }

    /**
     * @param string $source Currency to convert from
     * @param string $target Currency to convert to
     *
     * @throws CurrencyConversionException If the exchange rate is not available
     */
    public function get(string $source, string $target): ExchangeInterface
    {
        foreach ($this->exchanges as $exchange) {
            try {
                $exchange->getConversionRate($source, $target);

                return $exchange;
            } catch (CurrencyConversionException $e) {
                continue;
            }
        }

        throw CurrencyConversionException::exchangeRateNotAvailable($source, $target);
    }
}
