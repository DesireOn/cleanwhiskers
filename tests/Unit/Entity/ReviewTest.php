<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class ReviewTest extends TestCase
{
    public function testConstructSetsProperties(): void
    {
        $groomerUser = (new User())
            ->setEmail('groomer@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $city = new City('Sofia');
        $groomer = new GroomerProfile($groomerUser, $city, 'Biz', 'About');

        $author = (new User())
            ->setEmail('owner@example.com')
            ->setRoles([User::ROLE_PET_OWNER])
            ->setPassword('hash');

        $review = new Review($groomer, $author, 5, 'Great');

        self::assertSame($groomer, $review->getGroomer());
        self::assertSame($author, $review->getAuthor());
        self::assertSame(5, $review->getRating());
        self::assertSame('Great', $review->getComment());
        self::assertInstanceOf(\DateTimeImmutable::class, $review->getCreatedAt());
    }

    public function testConstructorRejectsInvalidRating(): void
    {
        $groomerUser = (new User())
            ->setEmail('groomer@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $city = new City('Sofia');
        $groomer = new GroomerProfile($groomerUser, $city, 'Biz', 'About');

        $author = (new User())
            ->setEmail('owner@example.com')
            ->setRoles([User::ROLE_PET_OWNER])
            ->setPassword('hash');

        $this->expectException(\InvalidArgumentException::class);
        new Review($groomer, $author, 6, 'Too good');
    }
}
