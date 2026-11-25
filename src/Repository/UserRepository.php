<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findTranslators(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles NOT LIKE :admin AND u.roles NOT LIKE :pm')
            ->setParameter('admin', '%ROLE_ADMIN%')
            ->setParameter('pm', '%ROLE_PROJECT_MANAGER%')
            ->getQuery()
            ->getResult();
    }

    public function findAdmins(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :admin OR u.roles LIKE :pm')
            ->setParameter('admin', '%ROLE_ADMIN%')
            ->setParameter('pm', '%ROLE_PROJECT_MANAGER%')
            ->getQuery()
            ->getResult();
    }
}
