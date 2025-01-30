<?php

namespace App\Doctrine;

use ApiPlatform\Metadata\Operation;
use App\Entity\Interface\LocalizedEntityInterface;
use App\Service\LocalizationService;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\Attribute\Required;

trait LocalizedExtensionTrait
{
    protected RequestStack $requestStack;

    protected LocalizationService $localizationService;

    #[Required]
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Required]
    public function setLocalizationService(LocalizationService $localizationService)
    {
        $this->localizationService = $localizationService;
    }

    public function supportsResult(string $resourceClass, ?Operation $operation = null, array $context = []): bool
    {
        $reflectionClass = new \ReflectionClass($resourceClass);

        return $reflectionClass->implementsInterface(LocalizedEntityInterface::class);
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

    private function getAcceptedLanguages(array $context): array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (\array_key_exists('request', $context)) {
            $request = $context['request'];
        }

        $tags = $request->headers->get('Accept-Language', '');

        return $this->localizationService->getLanguages($tags);
    }
}
