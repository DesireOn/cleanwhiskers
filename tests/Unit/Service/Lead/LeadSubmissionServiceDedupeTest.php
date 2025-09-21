<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Dto\Lead\LeadSubmissionDto;
use App\Entity\City;
use App\Entity\Lead;
use App\Entity\Service as GroomingService;
use App\Message\DispatchLeadMessage;
use App\Repository\CityRepository;
use App\Repository\ServiceRepository;
use App\Repository\LeadRepository;
use App\Service\Lead\LeadSubmissionService;
use App\Service\Lead\LeadTokenFactory;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class LeadSubmissionServiceDedupeTest extends TestCase
{
    /** @var CityRepository&MockObject */
    private CityRepository $cities;
    /** @var ServiceRepository&MockObject */
    private ServiceRepository $services;
    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $em;
    /** @var MessageBusInterface&MockObject */
    private MessageBusInterface $bus;
    private LeadTokenFactory $tokenFactory;

    protected function setUp(): void
    {
        $this->cities = $this->createMock(CityRepository::class);
        $this->services = $this->createMock(ServiceRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->tokenFactory = new LeadTokenFactory();
    }

    private function makeDto(): LeadSubmissionDto
    {
        $dto = new LeadSubmissionDto();
        $dto->citySlug = 'sofia';
        $dto->serviceSlug = 'mobile-dog-grooming';
        $dto->fullName = 'Owner';
        $dto->email = 'owner@example.com';
        $dto->phone = '555-123-4567';
        $dto->petType = 'Dog';
        $dto->breedSize = 'Small';
        $dto->consentToShare = true;
        return $dto;
    }

    private function seedLookups(): array
    {
        $city = new City('Sofia');
        $service = (new GroomingService())->setName('Mobile Dog Grooming');
        $this->cities->method('findOneBySlug')->with('sofia')->willReturn($city);
        $this->services->method('findOneBySlug')->with('mobile-dog-grooming')->willReturn($service);
        return [$city, $service];
    }

    public function testReturnsExistingRecentLeadAndDispatches(): void
    {
        $this->seedLookups();

        $existing = $this->createMock(Lead::class);
        $existing->method('getId')->willReturn(42);

        /** @var LeadRepository&MockObject $leadRepo */
        $leadRepo = $this->createMock(LeadRepository::class);
        $leadRepo->expects(self::once())
            ->method('findRecentByFingerprint')
            ->with($this->isType('string'), $this->isInstanceOf(\DateTimeImmutable::class))
            ->willReturn($existing);

        $this->em->method('getRepository')->with(Lead::class)->willReturn($leadRepo);

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with($this->isInstanceOf(DispatchLeadMessage::class))
            ->willReturn(new Envelope(new \stdClass()));

        $svc = new LeadSubmissionService($this->cities, $this->services, $this->em, $this->bus, $this->tokenFactory, 10);
        $result = $svc->create($this->makeDto());

        self::assertSame($existing, $result);
    }

    public function testUniqueViolationFallsBackToExisting(): void
    {
        $this->seedLookups();

        /** @var LeadRepository&MockObject $leadRepo */
        $leadRepo = $this->createMock(LeadRepository::class);
        $this->em->method('getRepository')->with(Lead::class)->willReturn($leadRepo);

        $dupe = $this->createMock(Lead::class);
        $dupe->method('getId')->willReturn(7);

        // First lookup returns null (no existing), then on catch it returns the dupe
        $leadRepo->expects(self::exactly(2))
            ->method('findRecentByFingerprint')
            ->willReturnOnConsecutiveCalls(null, $dupe);

        $this->em->expects(self::once())->method('persist');
        $this->em->expects(self::once())
            ->method('flush')
            ->willThrowException($this->createMock(UniqueConstraintViolationException::class));

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with($this->isInstanceOf(DispatchLeadMessage::class))
            ->willReturn(new Envelope(new \stdClass()));

        $svc = new LeadSubmissionService($this->cities, $this->services, $this->em, $this->bus, $this->tokenFactory, 10);
        $out = $svc->create($this->makeDto());
        self::assertSame($dupe, $out);
    }
}

