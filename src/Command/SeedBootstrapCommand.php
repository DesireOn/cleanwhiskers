<?php

declare(strict_types=1);

namespace App\Command;

use App\Seed\SeedDataset;
use App\Seed\Seeder;
use App\Seed\SeedPackProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'app:seed:bootstrap', description: 'Seed baseline data using a seed pack')]
final class SeedBootstrapCommand extends Command
{
    private string $appEnv;

    public function __construct(
        private readonly SeedPackProvider $packProvider,
        private readonly Seeder $seeder,
        KernelInterface $kernel,
    ) {
        $this->appEnv = $kernel->getEnvironment();
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('env-pack', null, InputOption::VALUE_REQUIRED, 'Seed pack to use (staging|production)')
            ->addOption('dry-run', null, InputOption::VALUE_NEGATABLE, 'Simulate without database writes')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Required to run in production');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $envPack = $input->getOption('env-pack');
        if (!is_string($envPack) || '' === $envPack) {
            $output->writeln('<error>The --env-pack option is required.</error>');

            return Command::INVALID;
        }

        if ('prod' === $this->appEnv && !$input->getOption('force')) {
            $output->writeln('<error>Refusing to run without --force in production.</error>');

            return Command::FAILURE;
        }

        $dryRunOption = $input->getOption('dry-run');
        $dryRun = null === $dryRunOption ? ('prod' === $this->appEnv) : (bool) $dryRunOption;

        $pack = $this->packProvider->get($envPack);

        $dataset = SeedDataset::default();

        if ($dryRun) {
            $output->writeln('<info>Dry run - no changes will be written.</info>');
            $output->writeln(sprintf('Cities: %d', count($dataset->cities)));
            $output->writeln(sprintf('Services: %d', count($dataset->services)));
            $output->writeln(sprintf('Users: %d', count($dataset->users)));
            $output->writeln(sprintf('Groomer profiles: %d', count($dataset->groomerProfiles)));

            return Command::SUCCESS;
        }

        $this->seeder->seed($dataset, $pack['withSamples']);
        $output->writeln('<info>Seeding completed.</info>');

        return Command::SUCCESS;
    }
}
