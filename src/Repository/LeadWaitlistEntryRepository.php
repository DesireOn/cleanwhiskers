<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Lead;
use App\Entity\LeadWaitlistEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LeadWaitlistEntry>
 */
class LeadWaitlistEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeadWaitlistEntry::class);
    }

    /**
     * @return array<int, LeadWaitlistEntry>
     */
    public function findByLead(Lead $lead): array
    {
        /** @var array<int, LeadWaitlistEntry> $rows */
        $rows = $this->findBy(['lead' => $lead], ['id' => 'ASC']);
        return $rows;
    }
}

