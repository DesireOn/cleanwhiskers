<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Testimonial;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import-gmb-reviews', description: 'Import Google My Business reviews as placeholder testimonials')]
final class ImportGMBReviewsCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('json', InputArgument::REQUIRED, 'Path to JSON file with reviews');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('json');
        if (!is_string($path) || !is_readable($path)) {
            $output->writeln('<error>JSON file not readable.</error>');

            return Command::FAILURE;
        }

        $rows = json_decode((string) file_get_contents($path), true);
        if (!is_array($rows)) {
            $output->writeln('<error>Invalid JSON structure.</error>');

            return Command::FAILURE;
        }

        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row['name'], $row['city'], $row['quote'])
                || !is_string($row['name']) || !is_string($row['city']) || !is_string($row['quote'])) {
                continue;
            }
            $testimonial = (new Testimonial($row['name'], $row['city'], $row['quote']))
                ->markPlaceholder();
            $this->em->persist($testimonial);
        }

        $this->em->flush();
        $output->writeln(sprintf('Imported %d reviews.', count($rows)));

        return Command::SUCCESS;
    }
}
