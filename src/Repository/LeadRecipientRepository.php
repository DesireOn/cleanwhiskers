<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Lead;
use App\Entity\LeadRecipient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LeadRecipient>
 */
class LeadRecipientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeadRecipient::class);
    }

    /**
     * @return array<int, LeadRecipient>
     */
    public function findByLead(Lead $lead): array
    {
        /** @var array<int, LeadRecipient> $rows */
        $rows = $this->findBy(['lead' => $lead], ['id' => 'ASC']);
        return $rows;
    }
}

