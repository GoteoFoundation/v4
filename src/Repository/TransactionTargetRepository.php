<?php

namespace App\Repository;

use App\Entity\TransactionTarget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransactionTarget>
 *
 * @method TransactionTarget|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransactionTarget|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransactionTarget[]    findAll()
 * @method TransactionTarget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionTargetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionTarget::class);
    }

//    /**
//     * @return TransactionTarget[] Returns an array of TransactionTarget objects
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

//    public function findOneBySomeField($value): ?TransactionTarget
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
