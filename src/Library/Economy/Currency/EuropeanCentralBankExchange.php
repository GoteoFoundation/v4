<?php

namespace App\Library\Economy\Currency;

use App\Entity\Money;
use App\Library\Economy\MoneyService;
use Brick\Math\RoundingMode;
use Brick\Money\CurrencyConverter;
use Brick\Money\ExchangeRateProvider;
use Brick\Money\ExchangeRateProvider\BaseCurrencyProvider;
use Brick\Money\ExchangeRateProvider\ConfigurableProvider;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Provides currency conversion using the daily updated exchanges rates by the European Central Bank.
 *
 * @see https://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/index.en.html
 */
class EuropeanCentralBankExchange implements ExchangeInterface
{
    public const NAME = 'european_central_bank';
    public const WEIGHT = 100;

    public const ISO_4217 = 'EUR';

    /**
     * ECB states that their rates are updated at around 16:00 CET every working day
     * This means the data has a time-to-live of a ~day.
     *
     * @see https://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/index.en.html
     */
    public const ECB_DATA_TTL = 86400;
    public const ECB_DATA = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    public const ECB_TIMEZONE = 'Europe/Berlin';

    private CacheInterface $cache;

    private \DateTimeInterface $date;

    private ExchangeRateProvider $provider;

    private CurrencyConverter $converter;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter();

        $data = $this->getData();

        $provider = new ConfigurableProvider();
        foreach ($data['Cube'] as $item) {
            $rate = $item['@attributes'];
            $provider->setExchangeRate(self::ISO_4217, $rate['currency'], $rate['rate']);
        }

        $this->date = $this->parseECBTime($data['@attributes']['time']);
        $this->provider = new BaseCurrencyProvider($provider, self::ISO_4217);
        $this->converter = new CurrencyConverter($this->provider);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getWeight(): int
    {
        return self::WEIGHT;
    }

    public function convert(Money $money, string $toCurrency): Money
    {
        $converted = $this->converter->convert(
            MoneyService::toBrick($money),
            $toCurrency,
            null,
            RoundingMode::HALF_EVEN
        );

        return new Money(
            $converted->getMinorAmount()->toInt(),
            $converted->getCurrency()->getCurrencyCode()
        );
    }

    public function getConversionRate(string $fromCurrency, string $toCurrency): float
    {
        return $this->provider->getExchangeRate($fromCurrency, $toCurrency)->toFloat();
    }

    public function getConversionDate(string $fromCurrency, string $toCurrency): \DateTimeInterface
    {
        $this->provider->getExchangeRate($fromCurrency, $toCurrency);

        return $this->date;
    }

    public function getData(): array
    {
        try {
            $cachedData = $this->getDataCached();
            $cachedDate = $this->parseECBTime($cachedData['@attributes']['time']);

            $currentDate = new \DateTime('now', new \DateTimeZone(self::ECB_TIMEZONE));
            $currentDayAt16 = (new \DateTime('now', new \DateTimeZone(self::ECB_TIMEZONE)))->setTime(16, 0);

            if (
                $currentDate > $currentDayAt16
                && $cachedDate < $currentDayAt16
            ) {
                $this->cache->delete(self::NAME);
                $cachedData = $this->getDataCached();
            }

            return $cachedData;
        } catch (\Exception $e) {
            return $this->getDataLatest();
        }
    }

    private function parseECBTime(string $time): \DateTimeInterface
    {
        return \DateTime::createFromFormat(
            \DateTimeInterface::RFC3339,
            \sprintf('%sT16:00:00+01:00', $time),
            new \DateTimeZone(self::ECB_TIMEZONE)
        );
    }

    private function getDataLatest(): array
    {
        $data = \simplexml_load_file(self::ECB_DATA);
        if (!$data) {
            throw new \Exception('Could not retrieve XML data');
        }

        return \json_decode(\json_encode($data), true)['Cube']['Cube'];
    }

    private function getDataCached(): array
    {
        $data = $this->cache->get(self::NAME, function (ItemInterface $item): array {
            $item->expiresAfter(self::ECB_DATA_TTL);

            return $this->getDataLatest();
        });

        if (!$data) {
            throw new \Exception('Could not retrieve cached data');
        }

        return $data;
    }
}
