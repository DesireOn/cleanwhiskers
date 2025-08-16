<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Entity\Blog\BlogPost;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class SeedBlogCommandTest extends KernelTestCase
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

    public function testSeedingIsIdempotent(): void
    {
        $application = new Application(self::$kernel);
        $command = $application->find('app:seed');
        $tester = new CommandTester($command);

        $tester->execute(['--blog' => true]);
        $tester->execute(['--blog' => true]);

        self::assertSame(1, $this->em->getRepository(BlogPost::class)->count([]));
    }
}
