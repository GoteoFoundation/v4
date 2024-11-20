<?php

namespace App\Repository\Accounting;

use App\Entity\Accounting\Accounting;
use App\Entity\Accounting\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * @return Transaction[] Transactions originated from or targeting the Accounting
     */
    public function findByAccounting(Accounting $accounting): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.origin = :val')
            ->orWhere('t.target = :val')
            ->setParameter('val', $accounting)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Transaction[] Transactions originated from the Accounting
     */
    public function findByOrigin(Accounting $origin): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.origin = :val')
            ->setParameter('val', $origin)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Transaction[] Transactions targetting the Accounting
     */
    public function findByTarget(Accounting $origin): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.target = :val')
            ->setParameter('val', $origin)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return Transaction[] Returns an array of Transaction objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Transaction
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
