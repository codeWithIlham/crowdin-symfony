<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findByOwner(User $user): array
    {
        return $this->findBy(['owner' => $user], ['createdAt' => 'DESC']);
    }

    public function findByTranslator(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.translators', 't')
            ->where('t = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Projets visibles par un traducteur :
     * - projet sans aucun traducteur assigné (ouvert à tous)
     * - OU projet où l'utilisateur est explicitement assigné
     */
    public function findVisibleForTranslator(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.translators', 't')
            ->where('t IS NULL')
            ->orWhere(':user MEMBER OF p.translators')
            ->setParameter('user', $user)
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();
    }
}
