<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\GroomerProfileRepository;
use App\Seeder\BlogSeed;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed', description: 'Seed demo data')]
final class SeedCommand extends Command
{
    public function __construct(
        private readonly BlogSeed $blogSeed,
        private readonly GroomerProfileRepository $groomerRepository,
        private readonly EntityManagerInterface $em,
    ) {
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

        $this->seedGroomerExtras($output);

        return Command::SUCCESS;
    }

    private function seedGroomerExtras(OutputInterface $output): void
    {
        $badgePool = ['New', 'Mobile', 'Verified'];
        $specialtyPool = [
            'Small dogs',
            'Large dogs',
            'Nail trimming',
            'Haircuts',
            'Cat grooming',
        ];

        foreach ($this->groomerRepository->findAll() as $profile) {
            if (random_int(1, 10) <= 8) {
                $profile->setBadges($this->sample($badgePool, random_int(1, 2)));
            }
            if (random_int(1, 10) <= 8) {
                $profile->setSpecialties($this->sample($specialtyPool, random_int(1, 3)));
            }
        }

        $this->em->flush();
        $output->writeln('<info>Groomer badges and specialties seeded.</info>');
    }

    /**
     * @param string[] $pool
     *
     * @return string[]
     */
    private function sample(array $pool, int $count): array
    {
        if ($count <= 0) {
            return [];
        }
        shuffle($pool);

        return array_slice($pool, 0, min($count, count($pool)));
    }
}
