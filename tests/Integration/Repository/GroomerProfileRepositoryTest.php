<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\User;
use App\Repository\GroomerProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GroomerProfileRepositoryTest extends KernelTestCase
{
    private GroomerProfileRepository $repository;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        $this->repository = $this->em->getRepository(GroomerProfile::class);
    }

    public function testFindOneBySlug(): void
    {
        $user = (new User())
            ->setEmail('groomer@example.com')
            ->setPassword('hash')
            ->setRoles([User::ROLE_GROOMER]);
        $city = new City('Sofia');

        $this->em->persist($user);
        $this->em->persist($city);
        $this->em->flush();

        $profile = new GroomerProfile($user, $city, 'Best Groomers', 'About us');
        $this->em->persist($profile);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findOneBySlug('best-groomers');
        self::assertNotNull($found);
        self::assertSame('Best Groomers', $found->getBusinessName());
    }
}
