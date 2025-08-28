<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\ImportGroomersCsvCommand;
use App\Entity\City;
use App\Repository\GroomerProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class GroomerImportCsvTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private GroomerProfileRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $this->repository = $container->get(GroomerProfileRepository::class);

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());

        $city = new City('Sofia');
        $city->refreshSlugFrom('sofia');
        $this->em->persist($city);
        $this->em->flush();
    }

    public function testImportIsIdempotent(): void
    {
        $csv = <<<CSV
name,city_slug,phone,service_area,services_offered,price
Paw Parlour,sofia,12345,Downtown,Full service,25
Other,unknown,999,,,
CSV;
        $path = sys_get_temp_dir().'/groomers.csv';
        file_put_contents($path, $csv);

        $command = static::getContainer()->get(ImportGroomersCsvCommand::class);
        $tester = new CommandTester($command);

        $status1 = $tester->execute(['csv' => $path]);
        self::assertSame(ImportGroomersCsvCommand::SUCCESS, $status1);
        $display1 = $tester->getDisplay();
        self::assertStringContainsString('Inserted: 1', $display1);
        self::assertStringContainsString('Updated: 0', $display1);
        self::assertStringContainsString('Skipped: 1', $display1);
        self::assertCount(1, $this->repository->findAll());
        $inserted = $this->repository->findOneBy(['businessName' => 'Paw Parlour']);
        self::assertNotNull($inserted);
        self::assertSame(25, $inserted->getPrice());

        $status2 = $tester->execute(['csv' => $path]);
        self::assertSame(ImportGroomersCsvCommand::SUCCESS, $status2);
        $display2 = $tester->getDisplay();
        self::assertStringContainsString('Inserted: 0', $display2);
        self::assertStringContainsString('Updated: 1', $display2);
        self::assertStringContainsString('Skipped: 1', $display2);
        self::assertCount(1, $this->repository->findAll());
    }
}
