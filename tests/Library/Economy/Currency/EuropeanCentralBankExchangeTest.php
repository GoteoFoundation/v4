<?php

namespace App\Tests\Library\Economy\Currency;

use App\Library\Economy\Currency\EuropeanCentralBankExchange;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class EuropeanCentralBankExchangeTest extends TestCase
{
    public function testGetsData()
    {
        $exchange = new EuropeanCentralBankExchange();
        $exchangeData = $exchange->getData();

        $this->assertIsArray($exchangeData);
        $this->assertArrayHasKey('Cube', $exchangeData);
        $this->assertArrayHasKey('@attributes', $exchangeData);
    }

    public function testStoresDataInCache()
    {
        $exchange = new EuropeanCentralBankExchange();
        $cache = new FilesystemAdapter();

        $exchangeData = $cache->get($exchange->getName(), function (): false {
            return false;
        });

        $this->assertNotFalse($exchangeData);
        $this->assertIsArray($exchangeData);
        $this->assertArrayHasKey('Cube', $exchangeData);
        $this->assertArrayHasKey('@attributes', $exchangeData);
    }
}
