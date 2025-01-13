<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Interface\LocalizedContentInterface;
use App\Mapping\AutoMapper;
use App\Service\LocalizationService;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Translatable\TranslatableListener;

class LocalizedContentProvider implements ProviderInterface
{
    /**
     * @param QueryItemExtensionInterface[]       $itemExtensions
     * @param QueryCollectionExtensionInterface[] $collectionExtensions
     */
    public function __construct(
        private AutoMapper $autoMapper,
        private ManagerRegistry $managerRegistry,
        private iterable $itemExtensions,
        private iterable $collectionExtensions,
        private LocalizationService $localizationService,
        private Pagination $pagination,
    ) {}

    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): ?object {
        $resourceClass = $operation->getClass();
        $entityClass = $this->getEntityClass($operation);

        $manager = $this->managerRegistry->getManagerForClass($entityClass);

        if ($manager === null) {
            throw new \RuntimeException();
        }

        $repository = $manager->getRepository($entityClass);

        if (!$repository instanceof EntityRepository) {
            throw new \RuntimeException();
        }

        $queryBuilder = $repository->createQueryBuilder('o');
        $queryNameGenerator = new QueryNameGenerator();

        if ($operation instanceof CollectionOperationInterface) {
            $collection = $this->provideCollection($queryBuilder, $queryNameGenerator, $entityClass, $operation, $context);

            $resources = [];
            foreach ($collection as $item) {
                $resources[] = $this->autoMapper->map($item, $resourceClass);
            }

            return new TraversablePaginator(
                new \ArrayIterator($resources),
                $this->pagination->getPage($context),
                $this->pagination->getLimit($operation, $context),
                \count($collection)
            );
        }

        $item = $this->provideItem($queryBuilder, $queryNameGenerator, $entityClass, $operation, $context);

        if (!$item) {
            return null;
        }

        return $this->autoMapper->map($item, $resourceClass);
    }

    /**
     * Determine wether an API operation is localizable or not.
     *
     * @param Operation $operation The operation as given to the providers
     *
     * @return bool `true` if the underlying entity is localizable
     */
    public function supports(Operation $operation): bool
    {
        $reflectionClass = new \ReflectionClass($this->getEntityClass($operation));

        return $reflectionClass->implementsInterface(LocalizedContentInterface::class);
    }

    private function getEntityClass(Operation $operation): string
    {
        /** @var Options */
        $options = $operation->getStateOptions();

        return $options->getEntityClass();
    }

    /**
     * Add a hint for each locale to the query.
     *
     * @param Query    $query   The query where the translation hints should be added
     * @param string[] $locales A list of the locales to be hinted
     *
     * @return Query The input query with added hints
     *
     * @see https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md#using-orm-query-hint
     */
    private function addTranslatableQueryHints(Query $query, array $locales): Query
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

        $query->setHint(TranslatableListener::HINT_FALLBACK, 0);
        $query->setHint(TranslatableListener::HINT_INNER_JOIN, true);

        return $query;
    }

    /**
     * @param array<string, mixed>|array{request: unset|\Symfony\Component\HttpFoundation\Request, resource_class: unset|string} $context
     */
    private function provideCollection(
        QueryBuilder $queryBuilder,
        QueryNameGenerator $queryNameGenerator,
        string $entityClass,
        Operation $operation,
        array $context = [],
    ) {
        $queryBuilder
            ->setFirstResult($this->pagination->getOffset($operation, $context))
            ->setMaxResults($this->pagination->getLimit($operation, $context));

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection(
                $queryBuilder,
                $queryNameGenerator,
                $entityClass,
                $operation,
                $context
            );
        }

        $languages = $context['request']->headers->get('Accept-Language', '');

        $query = $this->addTranslatableQueryHints(
            $queryBuilder->getQuery()->useQueryCache(true)->enableResultCache(),
            $this->localizationService->getLanguages($languages)
        );

        return $query->getResult();
    }

    /**
     * @param array<string, mixed>|array{request: unset|\Symfony\Component\HttpFoundation\Request, resource_class: unset|string} $context
     */
    private function provideItem(
        QueryBuilder $queryBuilder,
        QueryNameGenerator $queryNameGenerator,
        string $entityClass,
        Operation $operation,
        array $context = [],
    ) {
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->where($queryBuilder->expr()->eq(\sprintf('%s.id', $rootAlias), $context['uri_variables']['id']));

        foreach ($this->itemExtensions as $extension) {
            $extension->applyToItem(
                $queryBuilder,
                $queryNameGenerator,
                $entityClass,
                $context['uri_variables'],
                $operation,
                $context
            );
        }

        $languages = $context['request']->headers->get('Accept-Language', '');

        $query = $this->addTranslatableQueryHints(
            $queryBuilder->getQuery()->useQueryCache(true)->enableResultCache(),
            $this->localizationService->getLanguages($languages)
        );

        return $query->getOneOrNullResult();
    }
}
