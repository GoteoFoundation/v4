<?php

namespace App\Entity\Interface;

interface LocalizedContentInterface
{
    public function setTranslatableLocale(string $locale);
}
