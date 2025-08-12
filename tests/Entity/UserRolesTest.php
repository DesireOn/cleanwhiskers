<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserRolesTest extends TestCase
{
    public function testGetRolesAlwaysIncludesRoleUser(): void
    {
        $user = new User();
        self::assertSame(['ROLE_USER'], $user->getRoles());

        $user->setRoles([User::ROLE_GROOMER]);
        self::assertSame(['ROLE_GROOMER', 'ROLE_USER'], $user->getRoles());
    }

    public function testIsGroomerReturnsTrueWhenRolePresent(): void
    {
        $user = (new User())->withGroomerRole();
        self::assertTrue($user->isGroomer());
    }

    public function testIsOwnerReturnsTrueForDefaultUser(): void
    {
        $user = new User();
        self::assertTrue($user->isOwner());
    }

    public function testIsOwnerReturnsTrueWhenOwnerRoleSet(): void
    {
        $user = (new User())->setRoles([User::ROLE_PET_OWNER]);
        self::assertTrue($user->isOwner());
    }

    public function testIsOwnerReturnsFalseWhenOnlyGroomerRoleSet(): void
    {
        $user = (new User())->setRoles([User::ROLE_GROOMER]);
        self::assertFalse($user->isOwner());
    }

    public function testWithGroomerRoleAddsRole(): void
    {
        $user = new User();
        $user->withGroomerRole();
        self::assertContains(User::ROLE_GROOMER, $user->getRoles());
    }
}
