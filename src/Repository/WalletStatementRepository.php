<?php

namespace App\Repository;

use App\Entity\Accounting\Accounting;
use App\Entity\WalletStatement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WalletStatement>
 */
class WalletStatementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WalletStatement::class);
    }

    /**
     * @return WalletStatement[]
     */
    public function findByAccounting(Accounting $accounting): array
    {
        return $this->createQueryBuilder('w')
            ->join('w.transaction', 'wt', Join::WITH, 'wt.id = w.transaction')
            ->where('wt.origin = :val')
            ->orWhere('wt.target = :val')
            ->setParameter('val', $accounting->getId())
            ->orderBy('w.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return WalletStatement[]
     */
    public function findByOrigin(Accounting $accounting): array
    {
        return $this->createQueryBuilder('w')
            ->join('w.transaction', 'wt', Join::WITH, 'wt.id = w.transaction')
            ->andWhere('wt.origin = :val')
            ->setParameter('val', $accounting->getId())
            ->orderBy('w.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return WalletStatement[]
     */
    public function findByTarget(Accounting $accounting): array
    {
        return $this->createQueryBuilder('w')
            ->join('w.transaction', 'wt', Join::WITH, 'wt.id = w.transaction')
            ->andWhere('wt.target = :val')
            ->setParameter('val', $accounting->getId())
            ->orderBy('w.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return WalletStatement[] Returns an array of WalletStatement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?WalletStatement
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
