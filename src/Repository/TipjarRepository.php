<?php

namespace App\Repository;

use App\Entity\Tipjar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tipjar>
 *
 * @method Tipjar|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tipjar|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tipjar[]    findAll()
 * @method Tipjar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TipjarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tipjar::class);
    }

    //    /**
    //     * @return Tipjar[] Returns an array of Tipjar objects
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

    //    public function findOneBySomeField($value): ?Tipjar
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
