<?php

namespace App\Tests\Service;

use App\Service\LocalizationService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LocalizationServiceTest extends KernelTestCase
{
    private const TAGS = 'es-AR, es;q=0.9, en;q=0.8, de;q=0.7';

    private LocalizationService $localizationService;

    public function setUp(): void
    {
        self::bootKernel();

        $this->localizationService = static::getContainer()->get(LocalizationService::class);
    }

    public function testThrowsUnknownLanguageException()
    {
        $this->expectException(\Exception::class);

        $this->localizationService->getLanguage('*;q=0.5');
    }

    public function testThrowsUnknownLanguageExceptionInTagList()
    {
        $this->expectException(\Exception::class);

        $this->localizationService->getLanguages('fr-FR, *;q=0.5');
    }

    public function testGetsFirstLanguageFromTags()
    {
        $language = $this->localizationService->getLanguage(self::TAGS);

        $this->assertEquals('es', $language);
    }

    public function testGetsLanguagesFromTags()
    {
        $languages = $this->localizationService->getLanguages(self::TAGS);

        $this->assertIsArray($languages);
        $this->assertCount(4, $languages);

        $this->assertEquals('es', $languages[0]);
        $this->assertEquals('es', $languages[1]);
        $this->assertEquals('en', $languages[2]);
        $this->assertEquals('de', $languages[3]);
    }
}
