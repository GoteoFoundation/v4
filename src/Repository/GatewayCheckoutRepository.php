<?php

namespace App\Repository;

use App\Entity\GatewayCheckout;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GatewayCheckout>
 *
 * @method GatewayCheckout|null find($id, $lockMode = null, $lockVersion = null)
 * @method GatewayCheckout|null findOneBy(array $criteria, array $orderBy = null)
 * @method GatewayCheckout[]    findAll()
 * @method GatewayCheckout[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GatewayCheckoutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GatewayCheckout::class);
    }

    //    /**
    //     * @return GatewayCheckout[] Returns an array of GatewayCheckout objects
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

    //    public function findOneBySomeField($value): ?GatewayCheckout
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
