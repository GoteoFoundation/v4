<?php

namespace App\Tests\Service\Nominatim;

use App\Service\Nominatim\NominatimService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NominatimServiceTest extends KernelTestCase
{
    private NominatimService $nominatim;

    public function setUp(): void
    {
        self::bootKernel();

        $this->nominatim = static::getContainer()->get(NominatimService::class);
    }

    public function testSearch()
    {
        $search = $this->nominatim->search('Spain');

        $this->assertIsArray($search);
        $this->assertArrayHasKey('0', $search);
        $this->assertArrayHasKey('place_id', $search[0]);
        $this->assertArrayHasKey('address', $search[0]);
        $this->assertIsArray($search[0]['address']);
    }
}
