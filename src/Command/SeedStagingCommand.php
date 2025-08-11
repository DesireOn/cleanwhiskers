<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'app:seed-staging', description: 'Seed staging data')]
final class SeedStagingCommand extends Command
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Force run outside staging env');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $env = $this->kernel->getEnvironment();
        if ('staging' !== $env && !$input->getOption('force')) {
            $output->writeln('<error>Refusing to seed in non-staging environment. Use --force to override.</error>');

            return Command::FAILURE;
        }

        $app = $this->getApplication();
        if (null === $app) {
            throw new \RuntimeException('Application not available');
        }
        $app->setAutoExit(false);

        $app->run(new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
        ]), $output);

        $app->run(new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '--group' => ['staging'],
            '--no-interaction' => true,
        ]), $output);

        $counts = [
            'cities' => $this->em->getRepository(\App\Entity\City::class)->count([]),
            'services' => $this->em->getRepository(\App\Entity\Service::class)->count([]),
            'groomers' => $this->em->getRepository(\App\Entity\GroomerProfile::class)->count([]),
            'reviews' => $this->em->getRepository(\App\Entity\Review::class)->count([]),
            'bookings' => $this->em->getRepository(\App\Entity\BookingRequest::class)->count([]),
        ];

        $output->writeln(sprintf(
            'Seeded %d cities, %d services, %d groomers, %d reviews, %d bookings.',
            $counts['cities'],
            $counts['services'],
            $counts['groomers'],
            $counts['reviews'],
            $counts['bookings']
        ));

        return Command::SUCCESS;
    }
}
