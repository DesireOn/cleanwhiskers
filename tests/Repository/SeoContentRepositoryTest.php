<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\City;
use App\Entity\SeoContent;
use App\Entity\Service;
use App\Repository\SeoContentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SeoContentRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private SeoContentRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        $this->repository = $this->em->getRepository(SeoContent::class);
    }

    public function testFindOneByCityAndServiceReturnsContent(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Bath');
        $service->refreshSlugFrom($service->getName());
        $content = new SeoContent($city, $service, 'Title', 'Content');
        $content->setImagePath('../images/test.jpg');

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($content);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findOneByCityAndService($city, $service);
        self::assertInstanceOf(SeoContent::class, $found);
        self::assertSame('Title', $found->getTitle());
        self::assertSame('images/test.jpg', $found->getImagePath());
    }
}
