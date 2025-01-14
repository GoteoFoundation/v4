<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\AbstractPaginator;
use ApiPlatform\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Interface\LocalizedContentInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrineOrmPaginator;

/**
 * Adds localization hints to translatable entity queries.
 *
 * @see https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md#using-orm-query-hint
 */
final class LocalizedCollectionExtension implements QueryResultCollectionExtensionInterface
{
    use LocalizedContentTrait;
    use PaginationExtensionTrait;

    /**
     * Same priority as `api_platform.doctrine.orm.query_extension.pagination`.
     * This ensures other filters can have precedence over localization.
     *
     * @see https://api-platform.com/docs/core/extensions/#custom-doctrine-orm-extension
     */
    public static function getDefaultPriority(): int
    {
        return -64;
    }

    public function supportsResult(string $resourceClass, ?Operation $operation = null, array $context = []): bool
    {
        $reflectionClass = new \ReflectionClass($resourceClass);

        return $reflectionClass->implementsInterface(LocalizedContentInterface::class);
    }

    public function getResult(
        QueryBuilder $queryBuilder,
        ?string $resourceClass = null,
        ?Operation $operation = null,
        array $context = [],
    ): iterable {
        $query = $this->addLocalizationHints($queryBuilder, $this->getContextLanguages($context));

        if (\count($queryBuilder->getAllAliases()) === 1) {
            $query->setHint(CountWalker::HINT_DISTINCT, false);
        }

        $doctrineOrmPaginator = new DoctrineOrmPaginator($query, $this->shouldDoctrinePaginatorFetchJoinCollection($queryBuilder, $operation, $context));
        $doctrineOrmPaginator->setUseOutputWalkers($this->shouldDoctrinePaginatorUseOutputWalkers($queryBuilder, $operation, $context));

        $isPartialEnabled = $this->pagination->isPartialEnabled($operation, $context);

        if ($isPartialEnabled) {
            return new class($doctrineOrmPaginator) extends AbstractPaginator {};
        }

        return new Paginator($doctrineOrmPaginator);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
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
}
