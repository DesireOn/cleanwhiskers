<?php

declare(strict_types=1);

namespace App\Command;

use App\Seeder\BlogSeed;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed', description: 'Seed demo data')]
final class SeedCommand extends Command
{
    public function __construct(private readonly BlogSeed $blogSeed)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('blog', null, InputOption::VALUE_NONE, 'Seed blog demo content');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('blog')) {
            $this->blogSeed->seed();
            $output->writeln('<info>Blog content seeded.</info>');
        }

        return Command::SUCCESS;
    }
}
