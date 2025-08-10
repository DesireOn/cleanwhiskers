<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Domain\Shared\Exception\SlugCollisionException;
use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SlugListenerIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testPersistingDuplicateCitySlugThrowsException(): void
    {
        $city1 = new City('Sofia');
        $this->em->persist($city1);
        $this->em->flush();

        $city2 = new City('Sofia');
        $this->expectException(SlugCollisionException::class);
        $this->em->persist($city2);
        $this->em->flush();
    }
}
