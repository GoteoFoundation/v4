<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\AbstractPaginator;
use ApiPlatform\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use App\Entity\Interface\LocalizedContentInterface;
use App\Service\LocalizationService;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrineOrmPaginator;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Translatable\TranslatableListener;

/**
 * Adds localization hints to translatable entity queries.
 *
 * @see https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md#using-orm-query-hint
 */
final class LocalizedContentExtension implements QueryResultCollectionExtensionInterface
{
    use PaginationExtensionTrait;

    public function __construct(
        private LocalizationService $localizationService,
        private ManagerRegistry $managerRegistry,
        private Pagination $pagination,
    ) {}

    public function supportsResult(string $resourceClass, ?Operation $operation = null, array $context = []): bool
    {
        $reflectionClass = new \ReflectionClass($resourceClass);

        return $reflectionClass->implementsInterface(LocalizedContentInterface::class);
    }

    public function getResult(
        QueryBuilder $queryBuilder,
        ?string $resourceClass = null,
        ?Operation $operation = null,
        array $context = []
    ): iterable {
        $query = $this->addLocalizationHints($queryBuilder, $this->getContextLanguages($context));

        if (1 === \count($queryBuilder->getAllAliases())) {
            $query->setHint(CountWalker::HINT_DISTINCT, false);
        }

        $doctrineOrmPaginator = new DoctrineOrmPaginator($query, $this->shouldDoctrinePaginatorFetchJoinCollection($queryBuilder, $operation, $context));
        $doctrineOrmPaginator->setUseOutputWalkers($this->shouldDoctrinePaginatorUseOutputWalkers($queryBuilder, $operation, $context));

        $isPartialEnabled = $this->pagination->isPartialEnabled($operation, $context);

        if ($isPartialEnabled) {
            return new class($doctrineOrmPaginator) extends AbstractPaginator {
            };
        }

        return new Paginator($doctrineOrmPaginator);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (null === $pagination = $this->getPagination($queryBuilder, $operation, $context)) {
            return;
        }

        [$offset, $limit] = $pagination;

        $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getPagination(QueryBuilder $queryBuilder, ?Operation $operation, array $context): ?array
    {
        $enabled = isset($context['graphql_operation_name']) ? $this->pagination->isGraphQlEnabled($operation, $context) : $this->pagination->isEnabled($operation, $context);

        if (!$enabled) {
            return null;
        }

        if (isset($context['filters']['last']) && !isset($context['filters']['before'])) {
            $context['count'] = (new DoctrineOrmPaginator($queryBuilder))->count();
        }

        return \array_slice($this->pagination->getPagination($operation, $context), 1);
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
