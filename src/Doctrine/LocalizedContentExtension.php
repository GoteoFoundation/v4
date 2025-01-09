<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Interface\LocalizedContentInterface;
use Doctrine\ORM\Query;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\HttpFoundation\RequestStack;

final class LocalizedContentExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {}

    private function supports(string $resourceClass): bool
    {
        $reflection = new \ReflectionClass($resourceClass);

        return $reflection->implementsInterface(LocalizedContentInterface::class);
    }

    private function addHints(Query $query, string $locale)
    {
        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );

        $query->setHint(
            TranslatableListener::HINT_TRANSLATABLE_LOCALE,
            $locale
        );

        $query->setHint(
            TranslatableListener::HINT_FALLBACK,
            1
        );

        $query->getResult();
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (!$this->supports($resourceClass)) {
            return;
        }

        $this->addHints(
            $queryBuilder->getQuery(),
            $this->requestStack->getCurrentRequest()->getLocale()
        );
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (!$this->supports($resourceClass)) {
            return;
        }

        $this->addHints(
            $queryBuilder->getQuery(),
            $this->requestStack->getCurrentRequest()->getLocale()
        );
    }
}
