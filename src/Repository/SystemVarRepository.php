<?php

namespace App\Repository;

use App\Entity\SystemVar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SystemVar>
 *
 * @method SystemVar|null find($id, $lockMode = null, $lockVersion = null)
 * @method SystemVar|null findOneBy(array $criteria, array $orderBy = null)
 * @method SystemVar[]    findAll()
 * @method SystemVar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SystemVarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemVar::class);
    }

    //    /**
    //     * @return SystemVar[] Returns an array of SystemVar objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?SystemVar
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
