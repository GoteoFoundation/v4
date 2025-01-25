<?php

namespace App\Tests\Validator;

use App\Validator\CountrySubdivisionValidator;
use PHPUnit\Framework\TestCase;

class CountrySubdivionValidatorTest extends TestCase
{
    /**
     * @dataProvider isoValid
     */
    public function testValidatesISO3166_2(string $iso3166_2): void
    {
        $this->assertTrue(CountrySubdivisionValidator::validateISO3166_2($iso3166_2));
    }

    public function isoValid(): array
    {
        return [
            ['AR-D'],
            ['ES-AN'],
            ['ES-GR'],
            ['DE-NW'],
            ['ES-IB'],
            ['CN-SD'],
            ['IT-25'],
            ['FR-OCC'],
            ['PT-12'],
            ['RS-00'],
            ['BG-23'],
        ];
    }

    /**
     * @dataProvider isoAlike
     */
    public function testNotValidatesAlike(string $alike): void
    {
        $this->assertFalse(CountrySubdivisionValidator::validateISO3166_2($alike));
    }

    public function isoAlike(): array
    {
        return [
            ['NQ-01'],
            ['ES-01'],
            ['FR-ZZZ'],
            ['CA-A'],
            ['fu-fu'],
        ];
    }
}
