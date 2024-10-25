<?php

namespace App\Tests\Library\Economy;

use App\Entity\Money;
use App\Library\Economy\MoneyService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * WARNING: This test compares currencies, which change over time
 * and might cause the test to fail without justifiable cause in the code.
 * 
 * Test currencies and values have been selected for their stability to minimize chances of external factors such as
 * * Hyper inflation/deflation
 * * Currency intervention
 * * Demonetization
 */
class MoneyServiceTest extends KernelTestCase
{
    private MoneyService $moneyService;

    public function setUp(): void
    {
        self::bootKernel();

        $this->moneyService = static::getContainer()->get(MoneyService::class);
    }

    public function testComparesLess()
    {
        $a = new Money(300, 'EUR');
        $b = new Money(200, 'EUR');

        $this->assertTrue($this->moneyService->isLess($b, $a));
        $this->assertFalse($this->moneyService->isLess($a, $b));

        $c = new Money(100, 'JPY');

        // We are cooked if this fails and I'm not talking about code
        $this->assertTrue($this->moneyService->isLess($c, $a));
    }

    public function testComparesMore()
    {
        $a = new Money(100, 'GBP');
        $b = new Money(100, 'GBP');

        $this->assertTrue($this->moneyService->isMoreOrSame($a, $b));
        $this->assertTrue($this->moneyService->isMoreOrSame($b, $a));
        
        $c = new Money(300, 'GBP');

        $this->assertTrue($this->moneyService->isMoreOrSame($c, $b));
        $this->assertFalse($this->moneyService->isMoreOrSame($b, $c));

        $d = new Money(100, 'MXN');

        $this->assertFalse($this->moneyService->isMoreOrSame($d, $c));
        $this->assertFalse($this->moneyService->isMoreOrSame($d, $a));
    }
}
