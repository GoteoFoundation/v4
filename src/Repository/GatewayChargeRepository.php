<?php

namespace App\Repository;

use App\Entity\GatewayCharge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GatewayCharge>
 *
 * @method GatewayCharge|null find($id, $lockMode = null, $lockVersion = null)
 * @method GatewayCharge|null findOneBy(array $criteria, array $orderBy = null)
 * @method GatewayCharge[]    findAll()
 * @method GatewayCharge[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GatewayChargeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GatewayCharge::class);
    }

    //    /**
    //     * @return GatewayCharge[] Returns an array of GatewayCharge objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?GatewayCharge
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
