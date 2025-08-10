<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GroomerProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GroomerProfile>
 */
class GroomerProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroomerProfile::class);
    }

    public function findOneBySlug(string $slug): ?GroomerProfile
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
