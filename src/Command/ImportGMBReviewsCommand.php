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

#[AsCommand(
    name: 'app:import-gmb-reviews',
    description: 'Import Google My Business reviews as placeholder testimonials.'
)]
final class ImportGMBReviewsCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Path to CSV file with name,city,quote');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('file');
        if (!is_string($path) || !is_readable($path)) {
            $output->writeln('<error>File not readable.</error>');

            return Command::FAILURE;
        }

        $handle = fopen($path, 'rb');
        if (false === $handle) {
            $output->writeln('<error>Unable to open file.</error>');

            return Command::FAILURE;
        }

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 3) {
                continue;
            }
            [$name, $city, $quote] = $data;
            $testimonial = new Testimonial((string) $name, (string) $city, (string) $quote);
            $testimonial->markPlaceholder();
            $this->em->persist($testimonial);
        }

        fclose($handle);
        $this->em->flush();
        $output->writeln('<info>GMB reviews imported.</info>');

        return Command::SUCCESS;
    }
}
