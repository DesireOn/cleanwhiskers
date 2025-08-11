<?php

declare(strict_types=1);

namespace App\Tests\Unit\Seed;

use App\Entity\BookingRequest;
use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\Service;
use App\Entity\User;
use App\Seed\SeedDataset;
use App\Seed\Seeder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SeederIdempotencyTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private Seeder $seeder;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        $this->seeder = $container->get(Seeder::class);
    }

    public function testSeedingTwiceDoesNotDuplicateData(): void
    {
        $dataset = SeedDataset::default();

        $this->seeder->seed($dataset, true);
        $this->seeder->seed($dataset, true);

        self::assertSame(1, $this->em->getRepository(City::class)->count([]));
        self::assertSame(1, $this->em->getRepository(Service::class)->count([]));
        self::assertSame(2, $this->em->getRepository(User::class)->count([]));
        self::assertSame(1, $this->em->getRepository(GroomerProfile::class)->count([]));
        self::assertSame(1, $this->em->getRepository(Review::class)->count([]));
        self::assertSame(1, $this->em->getRepository(BookingRequest::class)->count([]));
    }
}
