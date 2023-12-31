<?php

namespace App\Library\Economy\Currency;

use App\Library\Economy\Monetizable;
use Brick\Math\RoundingMode;
use Brick\Money\CurrencyConverter;
use Brick\Money\ExchangeRateProvider;
use Brick\Money\ExchangeRateProvider\BaseCurrencyProvider;
use Brick\Money\ExchangeRateProvider\ConfigurableProvider;
use Brick\Money\Money;

/**
 * Provides currency conversion using the daily updated exchanges rates by the European Central Bank
 * @link https://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/index.en.html
 */
class EuropeanCentralBankExchange implements ExchangeInterface
{
    public const ISO_4217 = 'EUR';
    public const ECB_DATA = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    private \DateTimeInterface $date;
    private ExchangeRateProvider $provider;
    private CurrencyConverter $converter;

    public function __construct()
    {
        $data = json_decode(json_encode(\simplexml_load_file(self::ECB_DATA)), true)['Cube']['Cube'];

        $this->date = \DateTime::createFromFormat(
            \DateTimeInterface::RFC3339,
            sprintf('%sT16:00:00+01:00', $data['@attributes']['time']),
            new \DateTimeZone('CET')
        );


        $provider = new ConfigurableProvider();
        foreach ($data['Cube'] as $item) {
            $rate = $item['@attributes'];
            $provider->setExchangeRate(self::ISO_4217, $rate['currency'], $rate['rate']);
        }

        $this->provider = new BaseCurrencyProvider($provider, self::ISO_4217);
        $this->converter = new CurrencyConverter($this->provider);
    }

    public function getName(): string
    {
        return 'european_central_bank';
    }

    public function getWeight(): int
    {
        return 100;
    }

    public function getConversion(Monetizable $money, string $currency): Monetizable
    {
        return Monetizable::ofBrickMoney($this->converter->convert(
            Money::ofMinor($money->getAmount(), $money->getCurrency()),
            $currency,
            null,
            RoundingMode::HALF_EVEN
        ));
    }

    public function getConversionRate(string $source, string $target): float
    {
        return $this->provider->getExchangeRate($source, $target);
    }

    public function getConversionDate(string $source, string $target): \DateTimeInterface
    {
        $this->provider->getExchangeRate($source, $target);

        return $this->date;
    }
}
