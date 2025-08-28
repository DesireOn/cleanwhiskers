<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LeadCapture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LeadCapture>
 */
class LeadCaptureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeadCapture::class);
    }

    public function save(LeadCapture $lead): void
    {
        $em = $this->getEntityManager();
        $em->persist($lead);
        $em->flush();
    }
}
