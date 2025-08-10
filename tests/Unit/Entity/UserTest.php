<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testNewUserHasEmptyRoles(): void
    {
        $user = new User();
        self::assertSame([], $user->getRoles());
    }

    public function testCreatedAtIsInitialized(): void
    {
        $user = new User();
        self::assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }
}
