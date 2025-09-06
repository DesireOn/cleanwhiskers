<?php

declare(strict_types=1);

namespace App\Repository;

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

    public function findOneByRecipientToken(string $token): ?LeadRecipient
    {
        return $this->findOneBy(['recipientToken' => $token]);
    }
}
