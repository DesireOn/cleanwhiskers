<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\Service;
use App\Entity\LeadWaitlistEntry;
use App\Repository\LeadWaitlistEntryRepository;
use App\Service\Lead\LeadWaitlistManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LeadWaitlistManagerTest extends TestCase
{
    /** @var LeadWaitlistEntryRepository&MockObject */
    private LeadWaitlistEntryRepository $repo;
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(LeadWaitlistEntryRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
    }

    private function manager(): LeadWaitlistManager
    {
        return new LeadWaitlistManager($this->repo, $this->em);
    }

    private function makeLead(): Lead
    {
        $city = new City('Denver');
        $service = (new Service())->setName('Mobile Dog Grooming');
        return new Lead($city, $service, 'Jane PetOwner', 'owner@example.com');
    }

    public function testCreatesEntryWhenNotExisting(): void
    {
        $lead = $this->makeLead();
        $recipient = new LeadRecipient($lead, 'pro@example.com', 'hash', new \DateTimeImmutable('+1 hour'));

        $this->repo->method('findOneBy')->with(['lead' => $lead, 'leadRecipient' => $recipient])->willReturn(null);

        $persisted = [];
        $this->em->expects($this->atLeast(2))->method('persist')->willReturnCallback(function ($entity) use (&$persisted): void {
            $persisted[] = $entity;
        });
        $this->em->expects($this->once())->method('flush');

        $result = $this->manager()->join($lead, $recipient);

        $this->assertTrue($result['created']);
        $this->assertInstanceOf(LeadWaitlistEntry::class, $result['entry']);
        $this->assertNotEmpty($persisted, 'Should persist waitlist entry and audit log');
    }

    public function testReturnsExistingWhenAlreadyJoined(): void
    {
        $lead = $this->makeLead();
        $recipient = new LeadRecipient($lead, 'pro@example.com', 'hash', new \DateTimeImmutable('+1 hour'));
        $existing = new LeadWaitlistEntry($lead, $recipient);

        $this->repo->method('findOneBy')->with(['lead' => $lead, 'leadRecipient' => $recipient])->willReturn($existing);

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $result = $this->manager()->join($lead, $recipient);

        $this->assertFalse($result['created']);
        $this->assertSame($existing, $result['entry']);
    }
}

