<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Entity\Service as ServiceEntity;
use App\Repository\GroomerProfileRepository;
use App\Service\Lead\LeadSegmentationService;
use PHPUnit\Framework\TestCase;

final class LeadSegmentationServiceTest extends TestCase
{
    private GroomerProfileRepository $profiles;
    private LeadSegmentationService $service;

    protected function setUp(): void
    {
        $this->profiles = $this->createMock(GroomerProfileRepository::class);
        $this->service = new LeadSegmentationService($this->profiles);
    }

    private function makeLead(string $petType = 'dog'): Lead
    {
        $city = new City('Sofia');
        $service = (new ServiceEntity())->setName('Mobile Dog Grooming');
        return new Lead($city, $service, $petType, 'Alice', '+15550001111', bin2hex(random_bytes(8)));
    }

    private function makeProfile(?string $phone, array $specialties = []): GroomerProfile
    {
        $city = new City('Sofia');
        $p = new GroomerProfile(null, $city, 'Biz', 'About');
        $p->setPhone($phone);
        $p->setSpecialties($specialties);
        return $p;
    }

    public function testSegmentsWithPhoneFilter(): void
    {
        $lead = $this->makeLead('dog');

        $withPhone = $this->makeProfile('+15551234567');
        $noPhone = $this->makeProfile(null);

        $this->profiles
            ->method('findByCityAndService')
            ->willReturn([$withPhone, $noPhone]);

        $result = $this->service->segment($lead, limit: 10, mustHavePhone: true);

        self::assertSame([$withPhone], $result);
    }

    public function testSegmentsMatchingSpecialtyToPetType(): void
    {
        $lead = $this->makeLead('dog');

        $dogExpert = $this->makeProfile('+15551234567', ['Small Dogs', 'Poodles']);
        $catExpert = $this->makeProfile('+15559876543', ['Cats']);

        $this->profiles
            ->method('findByCityAndService')
            ->willReturn([$dogExpert, $catExpert]);

        $result = $this->service->segment($lead, limit: 10, mustHavePhone: true, matchSpecialtyToPetType: true);

        self::assertSame([$dogExpert], $result);
    }
}

