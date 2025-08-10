<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\BookingRequest;
use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Service;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class BookingRequestTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $groomerUser = (new User())
            ->setEmail('groomer@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $city = new City('Sofia');
        $groomer = new GroomerProfile($groomerUser, $city, 'Biz', 'About');

        $owner = (new User())
            ->setEmail('owner@example.com')
            ->setRoles([User::ROLE_PET_OWNER])
            ->setPassword('hash');

        $request = new BookingRequest($groomer, $owner);

        self::assertSame($groomer, $request->getGroomer());
        self::assertSame($owner, $request->getPetOwner());
        self::assertSame(BookingRequest::STATUS_PENDING, $request->getStatus());
        self::assertInstanceOf(\DateTimeImmutable::class, $request->getRequestedAt());

        $service = (new Service())
            ->setName('Bath');
        $service->refreshSlugFrom($service->getName());
        $request->setService($service);
        $request->setNotes('Please be gentle');
        $request->setStatus(BookingRequest::STATUS_ACCEPTED);

        self::assertSame($service, $request->getService());
        self::assertSame('Please be gentle', $request->getNotes());
        self::assertSame(BookingRequest::STATUS_ACCEPTED, $request->getStatus());
    }

    public function testSetStatusRejectsInvalid(): void
    {
        $groomerUser = (new User())
            ->setEmail('groomer@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $city = new City('Sofia');
        $groomer = new GroomerProfile($groomerUser, $city, 'Biz', 'About');

        $owner = (new User())
            ->setEmail('owner@example.com')
            ->setRoles([User::ROLE_PET_OWNER])
            ->setPassword('hash');

        $request = new BookingRequest($groomer, $owner);

        $this->expectException(\InvalidArgumentException::class);
        $request->setStatus('foo');
    }
}
