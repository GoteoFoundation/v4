<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Util\QueryChecker;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\Attribute\Required;

trait PaginationExtensionTrait
{
    protected Pagination $pagination;

    protected ManagerRegistry $managerRegistry;

    #[Required]
    public function setManagerRegistry(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    #[Required]
    public function setPagination(Pagination $pagination)
    {
        $this->pagination = $pagination;
    }

    /**
     * Determines the value of the $fetchJoinCollection argument passed to the Doctrine ORM Paginator.
     *
     * Ported from api-platform/core source code
     *
     * @see https://github.com/api-platform/core/blob/a11c21335297b6026aa2b12fbe11a77e888481d7/src/Doctrine/Orm/Extension/PaginationExtension.php#L128
     */
    private function shouldDoctrinePaginatorFetchJoinCollection(
        QueryBuilder $queryBuilder,
        ?Operation $operation = null,
        array $context = [],
    ): bool {
        $fetchJoinCollection = $operation?->getPaginationFetchJoinCollection();

        if (isset($context['operation_name']) && isset($fetchJoinCollection)) {
            return $fetchJoinCollection;
        }

        if (isset($context['graphql_operation_name']) && isset($fetchJoinCollection)) {
            return $fetchJoinCollection;
        }

        /*
         * "Cannot count query which selects two FROM components, cannot make distinction"
         *
         * @see https://github.com/doctrine/orm/blob/v2.6.3/lib/Doctrine/ORM/Tools/Pagination/WhereInWalker.php#L81
         * @see https://github.com/doctrine/doctrine2/issues/2910
         */
        if (QueryChecker::hasRootEntityWithCompositeIdentifier($queryBuilder, $this->managerRegistry)) {
            return false;
        }

        if (QueryChecker::hasJoinedToManyAssociation($queryBuilder, $this->managerRegistry)) {
            return true;
        }

        // disable $fetchJoinCollection by default (performance)
        return false;
    }

    /**
     * Determines whether the Doctrine ORM Paginator should use output walkers.
     *
     * Ported from api-platform/core source code
     *
     * @see https://github.com/api-platform/core/blob/a11c21335297b6026aa2b12fbe11a77e888481d7/src/Doctrine/Orm/Extension/PaginationExtension.php#L161
     */
    private function shouldDoctrinePaginatorUseOutputWalkers(QueryBuilder $queryBuilder, ?Operation $operation = null, array $context = []): bool
    {
        $useOutputWalkers = $operation?->getPaginationUseOutputWalkers();

        if (isset($context['operation_name']) && isset($useOutputWalkers)) {
            return $useOutputWalkers;
        }

        if (isset($context['graphql_operation_name']) && isset($useOutputWalkers)) {
            return $useOutputWalkers;
        }

        /*
         * "Cannot count query that uses a HAVING clause. Use the output walkers for pagination"
         *
         * @see https://github.com/doctrine/orm/blob/v2.6.3/lib/Doctrine/ORM/Tools/Pagination/CountWalker.php#L56
         */
        if (QueryChecker::hasHavingClause($queryBuilder)) {
            return true;
        }

        /*
         * "Cannot count query which selects two FROM components, cannot make distinction"
         *
         * @see https://github.com/doctrine/orm/blob/v2.6.3/lib/Doctrine/ORM/Tools/Pagination/CountWalker.php#L64
         */
        if (QueryChecker::hasRootEntityWithCompositeIdentifier($queryBuilder, $this->managerRegistry)) {
            return true;
        }

        /*
         * "Paginating an entity with foreign key as identifier only works when using the Output Walkers. Call Paginator#setUseOutputWalkers(true) before iterating the paginator."
         *
         * @see https://github.com/doctrine/orm/blob/v2.6.3/lib/Doctrine/ORM/Tools/Pagination/LimitSubqueryWalker.php#L77
         */
        if (QueryChecker::hasRootEntityWithForeignKeyIdentifier($queryBuilder, $this->managerRegistry)) {
            return true;
        }

        /*
         * "Cannot select distinct identifiers from query with LIMIT and ORDER BY on a column from a fetch joined to-many association. Use output walkers."
         *
         * @see https://github.com/doctrine/orm/blob/v2.6.3/lib/Doctrine/ORM/Tools/Pagination/LimitSubqueryWalker.php#L150
         */
        if (QueryChecker::hasMaxResults($queryBuilder) && QueryChecker::hasOrderByOnFetchJoinedToManyAssociation($queryBuilder, $this->managerRegistry)) {
            return true;
        }

        // Disable output walkers by default (performance)
        return false;
    }
}
