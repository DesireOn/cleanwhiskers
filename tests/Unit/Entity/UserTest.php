<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testNewUserHasDefaultUserRole(): void
    {
        $user = new User();
        self::assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testCreatedAtIsInitialized(): void
    {
        $user = new User();
        self::assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }
}
