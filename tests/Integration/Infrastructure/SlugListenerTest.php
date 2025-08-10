<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SlugListenerTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testCitySlugCollisionThrows(): void
    {
        $city1 = new City('Sofia');
        $this->em->persist($city1);
        $this->em->flush();

        self::assertSame('sofia', $city1->getSlug());

        $city2 = new City('Sofia');
        $this->expectException(\InvalidArgumentException::class);
        $this->em->persist($city2);
        $this->em->flush();
    }
}
