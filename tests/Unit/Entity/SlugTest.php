<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SlugTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testServiceSlugUnique(): void
    {
        $s1 = new Service();
        $s1->setName('Dog Wash');
        $s2 = new Service();
        $s2->setName('Dog Wash');
        $this->em->persist($s1);
        $this->em->flush();
        $this->em->persist($s2);
        $this->em->flush();

        self::assertSame('dog-wash', $s1->getSlug());
        self::assertSame('dog-wash-2', $s2->getSlug());
    }

    public function testGroomerSlugUsesCity(): void
    {
        $city = new City();
        $city->setName('Springfield');
        $user1 = new User();
        $user1->setEmail('a@example.com')->setPassword('x');
        $gp1 = new GroomerProfile();
        $gp1->setUser($user1)->setDisplayName('Puppy Cuts')->setDescription('desc')->setCity($city);

        $user2 = new User();
        $user2->setEmail('b@example.com')->setPassword('x');
        $gp2 = new GroomerProfile();
        $gp2->setUser($user2)->setDisplayName('Puppy Cuts')->setDescription('desc')->setCity($city);

        $this->em->persist($city);
        $this->em->persist($user1);
        $this->em->persist($gp1);
        $this->em->flush();

        $this->em->persist($user2);
        $this->em->persist($gp2);
        $this->em->flush();

        self::assertSame('puppy-cuts', $gp1->getSlug());
        self::assertSame('puppy-cuts-springfield-2', $gp2->getSlug());
    }
}
