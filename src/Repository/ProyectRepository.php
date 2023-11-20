<?php

namespace App\Repository;

use App\Entity\Proyect;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Proyect>
 *
 * @method Proyect|null find($id, $lockMode = null, $lockVersion = null)
 * @method Proyect|null findOneBy(array $criteria, array $orderBy = null)
 * @method Proyect[]    findAll()
 * @method Proyect[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProyectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Proyect::class);
    }

//    /**
//     * @return Proyect[] Returns an array of Proyect objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Proyect
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
