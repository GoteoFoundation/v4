<?php

namespace App\Tests\Library\Economy\Currency;

use App\Library\Economy\Currency\EuropeanCentralBankExchange;
use PHPUnit\Framework\TestCase;

class EuropeanCentralBankExchangeTest extends TestCase
{
    public function testGetsData()
    {
        $exchange = new EuropeanCentralBankExchange;
        $exchangeData = $exchange->getData();

        $this->assertIsArray($exchangeData);
        $this->assertArrayHasKey('Cube', $exchangeData);
        $this->assertArrayHasKey('@attributes', $exchangeData);
    }

    public function testStoresDataInApcuCache()
    {
        $exchange = new EuropeanCentralBankExchange;
        // Data should be available on instantiation

        $data = \apcu_fetch($exchange::ECB_DATA);

        $this->assertNotFalse($data);

        $exchangeData = \json_decode($data, true);
        
        $this->assertIsArray($exchangeData);
        $this->assertArrayHasKey('Cube', $exchangeData);
        $this->assertArrayHasKey('@attributes', $exchangeData);
    }
}
