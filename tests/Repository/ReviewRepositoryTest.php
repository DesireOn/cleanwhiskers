<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\User;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ReviewRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private ReviewRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        $this->repository = $this->em->getRepository(Review::class);
    }

    public function testFindByGroomerProfileOrderedByDate(): void
    {
        $city = new City('Sofia');
        $author = (new User())
            ->setEmail('owner@example.com')
            ->setPassword('hash');
        $groomerUser = (new User())
            ->setEmail('groomer@example.com')
            ->setPassword('hash')
            ->setRoles([User::ROLE_GROOMER]);
        $otherGroomerUser = (new User())
            ->setEmail('other@example.com')
            ->setPassword('hash')
            ->setRoles([User::ROLE_GROOMER]);

        $groomer = new GroomerProfile($groomerUser, $city, 'Best Groomers', 'About');
        $otherGroomer = new GroomerProfile($otherGroomerUser, $city, 'Other Groomers', 'About');

        $this->em->persist($city);
        $this->em->persist($author);
        $this->em->persist($groomerUser);
        $this->em->persist($otherGroomerUser);
        $this->em->persist($groomer);
        $this->em->persist($otherGroomer);
        $this->em->flush();

        $older = new Review($groomer, $author, 4, 'Old');
        $prop = new \ReflectionProperty(Review::class, 'createdAt');
        $prop->setAccessible(true);
        $prop->setValue($older, new \DateTimeImmutable('-1 day'));
        $newer = new Review($groomer, $author, 5, 'New');
        $otherReview = new Review($otherGroomer, $author, 3, 'Other');

        $this->em->persist($older);
        $this->em->persist($newer);
        $this->em->persist($otherReview);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findByGroomerProfileOrderedByDate($groomer);
        self::assertCount(2, $found);
        self::assertSame('New', $found[0]->getComment());
        self::assertSame('Old', $found[1]->getComment());
    }
}
