<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Dto\Lead\LeadSubmissionDto;
use App\Entity\City;
use App\Entity\Service as GroomingService;
use App\Message\DispatchLeadMessage;
use App\Repository\CityRepository;
use App\Repository\ServiceRepository;
use App\Service\Lead\LeadSubmissionService;
use App\Service\Lead\LeadTokenFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Envelope;

final class LeadSubmissionServiceTest extends TestCase
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

    public function testCreatePersistsLeadIssuesTokenAndDispatches(): void
    {
        $city = new City('Sofia');
        $service = (new GroomingService())->setName('Mobile Dog Grooming');
        $this->cities->method('findOneBySlug')->with('sofia')->willReturn($city);
        $this->services->method('findOneBySlug')->with('mobile-dog-grooming')->willReturn($service);

        // real factory will set token fields on the entity
        $this->em->expects(self::once())->method('persist');
        $this->em->expects(self::once())->method('flush');
        $this->bus->expects(self::once())->method('dispatch')
            ->with($this->isInstanceOf(DispatchLeadMessage::class))
            ->willReturn(new Envelope(new \stdClass()));

        $svc = new LeadSubmissionService($this->cities, $this->services, $this->em, $this->bus, $this->tokenFactory);
        $dto = new LeadSubmissionDto();
        $dto->citySlug = 'sofia';
        $dto->serviceSlug = 'mobile-dog-grooming';
        $dto->fullName = 'Owner';
        $dto->email = 'owner@example.com';
        $dto->phone = '555-123-4567';
        $dto->petType = 'Dog';
        $dto->breedSize = 'Small';
        $dto->consentToShare = true;

        $lead = $svc->create($dto);
        self::assertSame('Owner', $lead->getFullName());
        self::assertSame('owner@example.com', $lead->getEmail());
        self::assertSame('Dog', $lead->getPetType());
        self::assertSame('Small', $lead->getBreedSize());
        self::assertNotEmpty($lead->getOwnerTokenHash());
        self::assertInstanceOf(\DateTimeImmutable::class, $lead->getOwnerTokenExpiresAt());
    }

    public function testCreateThrowsOnInvalidCityOrService(): void
    {
        $this->cities->method('findOneBySlug')->willReturn(null);
        $this->services->method('findOneBySlug')->willReturn(null);
        $svc = new LeadSubmissionService($this->cities, $this->services, $this->em, $this->bus, $this->tokenFactory);

        $dto = new LeadSubmissionDto();
        $dto->citySlug = 'x';
        $dto->serviceSlug = 'y';
        $dto->fullName = 'Owner';
        $dto->phone = '5551234567';
        $dto->petType = 'Dog';

        $this->expectException(\InvalidArgumentException::class);
        $svc->create($dto);
    }
}
