<?php

namespace App\Repository;

use App\Entity\AccountingIncoming;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccountingIncoming>
 *
 * @method AccountingIncoming|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountingIncoming|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountingIncoming[]    findAll()
 * @method AccountingIncoming[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountingIncomingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountingIncoming::class);
    }

//    /**
//     * @return AccountingIncoming[] Returns an array of AccountingIncoming objects
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

//    public function findOneBySomeField($value): ?AccountingIncoming
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
