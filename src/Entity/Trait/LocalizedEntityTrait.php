<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait LocalizedEntityTrait
{
    #[Gedmo\Locale]
    private ?string $locale = null;

    #[ORM\Column()]
    private array $locales = [];

    public function setTranslatableLocale(string $locale)
    {
        $this->locale = $locale;

        $this->addLocale($locale);
    }

    public function getLocales(): array
    {
        return $this->locales;
    }

    public function addLocale(string $locale): static
    {
        $this->locales = \array_unique([...$this->locales, $locale]);

        return $this;
    }

    public function removeLocale(string $locale): static
    {
        $needle = $locale;

        $this->locales = [...\array_filter($this->locales, function (string $locale) use ($needle) {
            return $locale !== $needle;
        })];

        return $this;
    }

    public function setLocales(array $locales): static
    {
        $this->locales = $locales;

        return $this;
    }
}
