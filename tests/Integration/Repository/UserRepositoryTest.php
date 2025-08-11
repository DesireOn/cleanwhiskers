<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->repository = $container->get(UserRepository::class);
    }

    public function testUniqueEmailIsEnforced(): void
    {
        $user1 = (new User())
            ->setEmail('foo@example.com')
            ->setPassword('hash');
        $this->repository->save($user1, true);

        $user2 = (new User())
            ->setEmail('foo@example.com')
            ->setPassword('hash');
        $this->repository->save($user2);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->entityManager->flush();
    }
}
