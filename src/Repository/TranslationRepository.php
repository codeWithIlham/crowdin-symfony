<?php

namespace App\Repository;

use App\Entity\Language;
use App\Entity\Project;
use App\Entity\Translation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Translation::class);
    }

    public function findByProjectAndLanguage(Project $project, Language $language): array
    {
        return $this->findBy(['project' => $project, 'language' => $language]);
    }

    public function findByTranslator(User $user): array
    {
        return $this->findBy(['translator' => $user]);
    }

    public function countDoneByTranslator(User $user): int
    {
        return $this->count(['translator' => $user]);
    }
}
