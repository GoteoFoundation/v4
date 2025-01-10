<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Interface\LocalizedContentInterface;
use App\Service\LocalizationService;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Adds localization hints to translatable entity queries.
 *
 * @see https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md#using-orm-query-hint
 */
final class LocalizedContentExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private LocalizationService $localizationService,
    ) {}

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!$this->supports($resourceClass)) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        $this->addHints(
            $queryBuilder->getQuery(),
            $this->localizationService->getLanguages($request->headers->get('Accept-Language'))
        );
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!$this->supports($resourceClass)) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        $this->addHints(
            $queryBuilder->getQuery(),
            $this->localizationService->getLanguages($request->headers->get('Accept-Language'))
        );
    }

    private function supports(string $resourceClass): bool
    {
        $reflection = new \ReflectionClass($resourceClass);

        return $reflection->implementsInterface(LocalizedContentInterface::class);
    }

    private function addHints(Query $query, array $locales)
    {
        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );

        $locales = \array_reverse($locales);
        foreach ($locales as $locale) {
            $query->setHint(
                TranslatableListener::HINT_TRANSLATABLE_LOCALE,
                $locale
            );
        }

        $query->setHint(
            TranslatableListener::HINT_FALLBACK,
            1
        );

        $query->getResult();
    }
}
