<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsCommand(name: 'app:groomers:import-csv', description: 'Import unclaimed groomers from a CSV file')]
final class ImportGroomersCsvCommand extends Command
{
    public function __construct(
        private readonly CityRepository $cityRepository,
        private readonly GroomerProfileRepository $groomerRepository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('csv', InputArgument::REQUIRED, 'Path to CSV file');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('csv');
        if (!is_string($path) || !is_readable($path)) {
            $output->writeln('<error>CSV file not readable.</error>');

            return Command::FAILURE;
        }

        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $rowNum = 0;

        foreach ($file as $row) {
            if (!is_array($row)) {
                continue;
            }

            // Skip header
            if (0 === $rowNum++) {
                continue;
            }

            // Keep reading a 6th column for backward compatibility, but ignore it
            [$name, $citySlug, $phone, $serviceArea, $servicesOffered, $ignoredPriceRange] = array_pad($row, 6, null);

            if (!is_string($name) || !is_string($citySlug)) {
                ++$skipped;

                continue;
            }

            $phone = is_string($phone) ? $phone : null;
            $serviceArea = is_string($serviceArea) ? $serviceArea : null;
            $servicesOffered = is_string($servicesOffered) ? $servicesOffered : null;
            // priceRange removed: numeric price is the source of truth now

            $city = $this->cityRepository->findOneBySlug($citySlug);
            if (!$city instanceof City) {
                ++$skipped;

                continue;
            }

            $existing = $this->findExisting($name, $city);

            if (null !== $existing) {
                $this->applyDetails($existing, $phone, $serviceArea, $servicesOffered);
                $this->em->flush();
                ++$updated;

                continue;
            }

            $profile = new GroomerProfile(null, $city, $name, '');
            $profile->refreshSlugFrom($name);

            $suffix = 1;
            while ($this->groomerRepository->existsBySlug($profile->getSlug())) {
                $profile->refreshSlugFrom(sprintf('%s-%d', $name, $suffix));
                ++$suffix;
            }

            $this->applyDetails($profile, $phone, $serviceArea, $servicesOffered);

            $this->em->persist($profile);
            $this->em->flush();
            ++$inserted;
        }

        $output->writeln(sprintf('Inserted: %d', $inserted));
        $output->writeln(sprintf('Updated: %d', $updated));
        $output->writeln(sprintf('Skipped: %d', $skipped));

        return Command::SUCCESS;
    }

    private function findExisting(string $name, City $city): ?GroomerProfile
    {
        $slug = $this->slugify($name);
        $existing = $this->groomerRepository->findOneBySlug($slug);
        if (null !== $existing) {
            return $existing;
        }

        return $this->groomerRepository->findOneBy([
            'businessName' => $name,
            'city' => $city,
        ]);
    }

    private function applyDetails(
        GroomerProfile $profile,
        ?string $phone,
        ?string $serviceArea,
        ?string $servicesOffered,
    ): void {
        $profile->setPhone($this->nullOrTrim($phone));
        $profile->setServiceArea($this->nullOrTrim($serviceArea));
        $profile->setServicesOffered($this->nullOrTrim($servicesOffered));
    }

    private function nullOrTrim(?string $value): ?string
    {
        $trimmed = null === $value ? null : trim($value);

        return '' === $trimmed ? null : $trimmed;
    }

    private function slugify(string $source): string
    {
        $normalized = preg_replace('/\s+/', ' ', mb_strtolower(trim($source))) ?? '';

        return (new AsciiSlugger())->slug($normalized)->lower()->toString();
    }
}
