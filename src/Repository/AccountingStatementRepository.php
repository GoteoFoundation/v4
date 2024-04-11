<?php

namespace App\Repository;

use App\Entity\AccountingStatement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccountingStatement>
 *
 * @method AccountingStatement|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountingStatement|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountingStatement[]    findAll()
 * @method AccountingStatement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountingStatementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountingStatement::class);
    }

    //    /**
    //     * @return AccountingStatement[] Returns an array of AccountingStatement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?AccountingStatement
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
