<?php

namespace App\Library\Economy\Currency;

use Brick\Money\Exception\CurrencyConversionException;

class ExchangeLocator
{
    /**
     * @var ExchangeInterface[]
     */
    private array $exchanges;

    /**
     * @param iterable<int, ExchangeInterface> $exchanges
     */
    public function __construct(iterable $exchanges)
    {
        /** @var ExchangeInterface[] */
        $exchanges = \iterator_to_array($exchanges);

        /** 
         * @param ExchangeInterface $a
         * @param ExchangeInterface $b
         */
        usort($exchanges, function ($a, $b) {
            return $a->getWeight() < $b->getWeight();
        });

        foreach ($exchanges as $exchange) {
            $this->exchanges[$exchange->getId()] = $exchange;
        }
    }

    /**
     * @return ExchangeInterface[]
     */
    public function getExchanges(): array
    {
        return $this->exchanges;
    }

    /**
     * @param string $id ID of the Exchange Interface implementation
     * @return ExchangeInterface
     */
    public function getExchange(string $id): ExchangeInterface
    {
        return $this->exchanges[$id];
    }

    /**
     * @param string $source Currency to convert from
     * @param string $target Currency to convert to
     * @return ExchangeInterface
     * @throws CurrencyConversionException If the exchange rate is not available
     */
    public function getExchangeFor(string $source, string $target): ExchangeInterface
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
