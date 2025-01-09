<?php

namespace App\Entity\Trait;

use Gedmo\Mapping\Annotation as Gedmo;

trait LocalizedContent
{
    #[Gedmo\Locale]
    private ?string $locale = null;

    public function setTranslatableLocale(string $locale)
    {
        $this->locale = $locale;
    }
}
