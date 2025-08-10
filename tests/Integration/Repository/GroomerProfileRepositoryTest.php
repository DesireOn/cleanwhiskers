<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Service;
use App\Entity\User;
use App\Repository\GroomerProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GroomerProfileRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private GroomerProfileRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repository = $container->get(GroomerProfileRepository::class);
    }

    public function testFindByCityAndService(): void
    {
        $city = new City();
        $city->setName('Springfield');
        $service = new Service();
        $service->setName('Bathing');

        $user = new User();
        $user->setEmail('c@example.com')->setPassword('x');
        $groomer = new GroomerProfile();
        $groomer->setUser($user)->setDisplayName('Groomer A')->setDescription('desc')->setCity($city)->addService($service);

        $otherCity = new City();
        $otherCity->setName('Shelbyville');
        $otherService = new Service();
        $otherService->setName('Clipping');
        $user2 = new User();
        $user2->setEmail('d@example.com')->setPassword('x');
        $otherGroomer = new GroomerProfile();
        $otherGroomer->setUser($user2)->setDisplayName('Groomer B')->setDescription('desc')->setCity($otherCity)->addService($otherService);

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($user);
        $this->em->persist($groomer);
        $this->em->persist($otherCity);
        $this->em->persist($otherService);
        $this->em->persist($user2);
        $this->em->persist($otherGroomer);
        $this->em->flush();

        $result = $this->repository->findByCityAndService('springfield', 'bathing');
        self::assertCount(1, $result);
        self::assertSame($groomer->getId(), $result[0]->getId());

        // ensure service deletion detaches
        $this->em->remove($service);
        $this->em->flush();
        $this->em->refresh($groomer);
        self::assertCount(0, $groomer->getServices());
    }
}
