<?php

namespace App\Doctrine;

use App\Service\LocalizationService;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Contracts\Service\Attribute\Required;

trait LocalizedContentTrait
{
    protected LocalizationService $localizationService;

    #[Required]
    public function setLocalizationService(LocalizationService $localizationService)
    {
        $this->localizationService = $localizationService;
    }

    private function addLocalizationHints(QueryBuilder $queryBuilder, array $locales): Query
    {
        $query = $queryBuilder->getQuery();

        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );

        \array_reverse($locales);
        foreach ($locales as $locale) {
            $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale);
        }

        $query->setHint(TranslatableListener::HINT_FALLBACK, 1);

        return $query;
    }

    private function getContextLanguages(array $context): array
    {
        $tags = $context['request']->headers->get('Accept-Language', '');

        return $this->localizationService->getLanguages($tags);
    }
}