<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\Service as ServiceEntity;
use App\Repository\LeadRecipientRepository;
use App\Repository\LeadRepository;
use App\Service\Lead\LeadClaimService;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class LeadClaimServiceTest extends TestCase
{
    private EntityManagerInterface $em;
    private LeadRepository $leadRepo;
    private LeadRecipientRepository $recipientRepo;
    private LeadClaimService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->leadRepo = $this->createMock(LeadRepository::class);
        $this->recipientRepo = $this->createMock(LeadRecipientRepository::class);
        $this->service = new LeadClaimService($this->em, $this->leadRepo, $this->recipientRepo);
    }

    private function makeLeadAndRecipient(): array
    {
        $city = new City('Sofia');
        $service = (new ServiceEntity())->setName('Mobile Dog Grooming');
        $lead = new Lead($city, $service, 'dog', 'Alice', '+15550001111', 'leadtoken');

        $groomer = new GroomerProfile(null, $city, 'Biz', 'About');
        $groomer->setPhone('+15551234567');

        $recipient = new LeadRecipient($lead, $groomer, '+15551234567', 'rectoken');

        return [$lead, $recipient];
    }

    public function testClaimWinnerReturnsTrue(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();

        $this->leadRepo->method('findOneByClaimToken')->with('leadtoken')->willReturn($lead);
        $this->recipientRepo->method('findOneByRecipientToken')->with('rectoken')->willReturn($recipient);

        $qb = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        $qb->method('update')->willReturn($qb);
        $qb->method('set')->willReturn($qb);
        $qb->method('where')->willReturn($qb);
        $qb->method('andWhere')->willReturn($qb);
        $qb->method('setParameter')->willReturn($qb);
        $qb->method('getQuery')->willReturn($query);

        $query->method('execute')->willReturn(1);

        $this->em->method('createQueryBuilder')->willReturn($qb);

        self::assertTrue($this->service->claim('leadtoken', 'rectoken'));
    }

    public function testClaimLoserReturnsFalseWhenAlreadyClaimed(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();

        $this->leadRepo->method('findOneByClaimToken')->with('leadtoken')->willReturn($lead);
        $this->recipientRepo->method('findOneByRecipientToken')->with('rectoken')->willReturn($recipient);

        $qb = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        $qb->method('update')->willReturn($qb);
        $qb->method('set')->willReturn($qb);
        $qb->method('where')->willReturn($qb);
        $qb->method('andWhere')->willReturn($qb);
        $qb->method('setParameter')->willReturn($qb);
        $qb->method('getQuery')->willReturn($query);

        $query->method('execute')->willReturn(0);

        $this->em->method('createQueryBuilder')->willReturn($qb);

        self::assertFalse($this->service->claim('leadtoken', 'rectoken'));
    }

    public function testThrowsOnRecipientMismatch(): void
    {
        [$lead, $recipient] = $this->makeLeadAndRecipient();

        // Create a different lead to mismatch and force different IDs
        $city = new City('Varna');
        $service = (new ServiceEntity())->setName('Mobile Dog Grooming');
        $otherLead = new Lead($city, $service, 'dog', 'Bob', '+15550002222', 'anothertoken');

        // Force IDs via reflection to avoid both being null
        $idProp = (new \ReflectionClass(Lead::class))->getProperty('id');
        $idProp->setAccessible(true);
        $idProp->setValue($lead, 1);
        $idProp->setValue($otherLead, 2);

        $this->leadRepo->method('findOneByClaimToken')->with('anothertoken')->willReturn($otherLead);
        $this->recipientRepo->method('findOneByRecipientToken')->with('rectoken')->willReturn($recipient);

        $this->expectException(\RuntimeException::class);
        $this->service->claim('anothertoken', 'rectoken');
    }
}
