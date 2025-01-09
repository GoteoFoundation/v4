<?php

namespace App\Entity\Interface;

interface LocalizedContentInterface
{
    public function setTranslatableLocale(string $locale);

    public function getLocales(): array;

    public function addLocale(string $locale): static;

    public function setLocales(array $locales): static;
}
