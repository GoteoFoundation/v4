<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryResultItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

/**
 * Adds localization hints to translatable entity queries.
 *
 * @see https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md#using-orm-query-hint
 */
final class LocalizedItemExtension implements QueryResultItemExtensionInterface
{
    use LocalizedContentTrait;

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

    public function getResult(
        QueryBuilder $queryBuilder,
        ?string $resourceClass = null,
        ?Operation $operation = null,
        array $context = [],
    ): ?object {
        $query = $this->addLocalizationHints($queryBuilder, $this->getContextLanguages($context));

        return $query->getOneOrNullResult();
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {}
}
