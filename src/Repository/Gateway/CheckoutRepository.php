<?php

namespace App\Repository\Gateway;

use App\Entity\Gateway\Checkout;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Checkout>
 *
 * @method Checkout|null find($id, $lockMode = null, $lockVersion = null)
 * @method Checkout|null findOneBy(array $criteria, array $orderBy = null)
 * @method Checkout[]    findAll()
 * @method Checkout[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckoutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Checkout::class);
    }

    /**
     * @return Checkout|null
     */
    public function findOneByTracking(string $title, string $value): array
    {
        return $this->createQueryBuilder('g')
            ->join('g.gatewayTrackings', 'gt', Join::WITH, 'gt.checkout = g.id')
            ->andWhere('gt.value = :val')
            ->andWhere('gt.title = :title')
            ->setParameter('val', $value)
            ->setParameter('title', $title)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    //    /**
    //     * @return Checkout[] Returns an array of Checkout objects
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

    //    public function findOneBySomeField($value): ?Checkout
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
