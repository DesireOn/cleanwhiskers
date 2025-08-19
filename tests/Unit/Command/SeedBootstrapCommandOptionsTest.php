<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\SeedBootstrapCommand;
use App\Repository\BookingRequestRepository;
use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use App\Repository\ReviewRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Seed\Seeder;
use App\Seed\SeedPackProvider;
use App\Seed\SeedDataset;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\KernelInterface;

final class SeedBootstrapCommandOptionsTest extends TestCase
{
    public function testFailsWithoutForceInProd(): void
    {
        $provider = new SeedPackProvider(new ParameterBag([
            'seed.packs' => [
                'production' => ['entities' => ['default'], 'withSamples' => false],
            ],
        ]));

        $seeder = new Seeder(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(CityRepository::class),
            $this->createMock(ServiceRepository::class),
            $this->createMock(UserRepository::class),
            $this->createMock(GroomerProfileRepository::class),
            $this->createMock(ReviewRepository::class),
            $this->createMock(BookingRequestRepository::class),
        );

        $command = new SeedBootstrapCommand($provider, $seeder, $this->kernelMock('prod'));

        $tester = new CommandTester($command);
        $status = $tester->execute(['--env-pack' => 'production']);

        self::assertSame(SeedBootstrapCommand::FAILURE, $status);
        self::assertStringContainsString('Refusing to run', $tester->getDisplay());
    }

    public function testDryRunByDefaultInProd(): void
    {
        $provider = new SeedPackProvider(new ParameterBag([
            'seed.packs' => [
                'production' => ['entities' => ['default'], 'withSamples' => false],
            ],
        ]));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('wrapInTransaction');

        $seeder = new Seeder(
            $em,
            $this->createMock(CityRepository::class),
            $this->createMock(ServiceRepository::class),
            $this->createMock(UserRepository::class),
            $this->createMock(GroomerProfileRepository::class),
            $this->createMock(ReviewRepository::class),
            $this->createMock(BookingRequestRepository::class),
        );

        $command = new SeedBootstrapCommand($provider, $seeder, $this->kernelMock('prod'));
        $tester = new CommandTester($command);
        $status = $tester->execute([
            '--env-pack' => 'production',
            '--force' => true,
        ]);

        self::assertSame(SeedBootstrapCommand::SUCCESS, $status);
        $display = $tester->getDisplay();
        self::assertStringContainsString('Dry run', $display);
        $cityCount = count(SeedDataset::default()->cities);
        self::assertStringContainsString(sprintf('Cities: %d', $cityCount), $display);
    }

    private function kernelMock(string $env): KernelInterface
    {
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn($env);

        return $kernel;
    }
}
