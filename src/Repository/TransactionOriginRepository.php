<?php

namespace App\Repository;

use App\Entity\TransactionOrigin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransactionOrigin>
 *
 * @method TransactionOrigin|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransactionOrigin|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransactionOrigin[]    findAll()
 * @method TransactionOrigin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionOriginRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionOrigin::class);
    }

//    /**
//     * @return TransactionOrigin[] Returns an array of TransactionOrigin objects
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

//    public function findOneBySomeField($value): ?TransactionOrigin
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
