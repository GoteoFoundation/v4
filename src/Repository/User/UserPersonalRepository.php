<?php

namespace App\Repository\User;

use App\Entity\User\UserPersonal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserPersonal>
 *
 * @method UserPersonal|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPersonal|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserPersonal[]    findAll()
 * @method UserPersonal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserPersonalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPersonal::class);
    }
}
