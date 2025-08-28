<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Testimonial;
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

    public function testFindLatestWithFallbackPrioritizesReal(): void
    {
        $placeholder = new Testimonial('Placeholder', 'City', 'Placeholder quote');
        $placeholder->markPlaceholder();
        $prop = new \ReflectionProperty(Testimonial::class, 'createdAt');
        $prop->setAccessible(true);
        $prop->setValue($placeholder, new \DateTimeImmutable());

        $real = new Testimonial('Real', 'City', 'Real quote');
        $prop->setValue($real, new \DateTimeImmutable('-1 day'));

        $this->em->persist($placeholder);
        $this->em->persist($real);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findLatestWithFallback(1);
        self::assertCount(1, $found);
        self::assertSame('Real quote', $found[0]->getQuote());
    }

    public function testFindLatestWithFallbackIncludesPlaceholdersWhenNeeded(): void
    {
        $real = new Testimonial('Real', 'City', 'Real quote');
        $placeholder = new Testimonial('Placeholder', 'City', 'Placeholder quote');
        $placeholder->markPlaceholder();
        $prop = new \ReflectionProperty(Testimonial::class, 'createdAt');
        $prop->setAccessible(true);
        $prop->setValue($placeholder, new \DateTimeImmutable());
        $prop->setValue($real, new \DateTimeImmutable('-1 day'));

        $this->em->persist($real);
        $this->em->persist($placeholder);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findLatestWithFallback(2);
        self::assertCount(2, $found);
        self::assertSame('Real quote', $found[0]->getQuote());
        self::assertSame('Placeholder quote', $found[1]->getQuote());
    }
}
