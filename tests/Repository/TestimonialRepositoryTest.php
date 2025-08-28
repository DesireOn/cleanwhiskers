<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\Testimonial;
use App\Entity\User;
use App\Repository\TestimonialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TestimonialRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private TestimonialRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        $this->repository = $this->em->getRepository(Testimonial::class);
    }

    public function testReturnsReviewWhenAvailable(): void
    {
        $city = new City('Sofia');
        $author = (new User())
            ->setEmail('owner@example.com')
            ->setPassword('hash');
        $groomerUser = (new User())
            ->setEmail('groomer@example.com')
            ->setPassword('hash')
            ->setRoles([User::ROLE_GROOMER]);
        $groomer = new GroomerProfile($groomerUser, $city, 'Best Groomer', 'About');
        $review = new Review($groomer, $author, 5, 'Real review');
        $placeholder = (new Testimonial('A', 'Sofia', 'Placeholder'))->markPlaceholder();

        $this->em->persist($city);
        $this->em->persist($author);
        $this->em->persist($groomerUser);
        $this->em->persist($groomer);
        $this->em->persist($review);
        $this->em->persist($placeholder);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findOneForGroomer($groomer);
        self::assertNotNull($found);
        self::assertSame('Real review', $found['excerpt']);
        self::assertFalse($found['is_placeholder']);
    }

    public function testFallsBackToPlaceholder(): void
    {
        $city = new City('Varna');
        $groomerUser = (new User())
            ->setEmail('g2@example.com')
            ->setPassword('hash')
            ->setRoles([User::ROLE_GROOMER]);
        $groomer = new GroomerProfile($groomerUser, $city, 'No Reviews', 'About');
        $placeholder = (new Testimonial('B', 'Varna', 'Placeholder quote'))->markPlaceholder();

        $this->em->persist($city);
        $this->em->persist($groomerUser);
        $this->em->persist($groomer);
        $this->em->persist($placeholder);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findOneForGroomer($groomer);
        self::assertNotNull($found);
        self::assertSame('Placeholder quote', $found['excerpt']);
        self::assertTrue($found['is_placeholder']);
    }
}
