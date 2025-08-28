<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\City;
use App\Entity\Testimonial;
use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use App\Repository\TestimonialRepository;
use App\Seed\SeedDataset;
use App\Seed\Seeder;
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
    /**
     * @var array<int, array{name:string, coverageNotes:string}>
     */
    private const POPULAR_CITIES = [
        ['name' => 'Sofia', 'coverageNotes' => 'Capital and biggest coverage area'],
        ['name' => 'Plovdiv', 'coverageNotes' => 'Strong groomer presence'],
        ['name' => 'Varna', 'coverageNotes' => 'Coastal coverage'],
        ['name' => 'Ruse', 'coverageNotes' => 'Danube region'],
        ['name' => 'Burgas', 'coverageNotes' => 'Black Sea coverage'],
    ];

    public function __construct(
        private readonly BlogSeed $blogSeed,
        private readonly GroomerProfileRepository $groomerRepository,
        private readonly TestimonialRepository $testimonialRepository,
        private readonly EntityManagerInterface $em,
        private readonly Seeder $seeder,
        private readonly CityRepository $cityRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('blog', null, InputOption::VALUE_NONE, 'Seed blog demo content');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->seedPopularCities($output);

        if ($input->getOption('blog')) {
            $this->blogSeed->seed();
            $output->writeln('<info>Blog content seeded.</info>');
        }

        $this->seedGroomerExtras($output);
        $this->seedTestimonials($output);

        return Command::SUCCESS;
    }

    private function seedPopularCities(OutputInterface $output): void
    {
        $this->seeder->seed(SeedDataset::default());

        foreach (self::POPULAR_CITIES as $cityData) {
            $city = $this->cityRepository->findOneBy(['name' => $cityData['name']]);
            if (null === $city) {
                $city = new City($cityData['name']);
                $city->refreshSlugFrom($cityData['name']);
                $this->em->persist($city);
            }
            $city->setCoverageNotes($cityData['coverageNotes']);
        }

        $this->em->flush();
        $output->writeln('<info>Popular cities seeded.</info>');
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
            if ([] === $profile->getBadges() && random_int(1, 10) <= 8) {
                $profile->setBadges($this->sample($badgePool, random_int(1, 2)));
            }
            if ([] === $profile->getSpecialties() && random_int(1, 10) <= 8) {
                $profile->setSpecialties($this->sample($specialtyPool, random_int(1, 3)));
            }
        }

        $this->em->flush();
        $output->writeln('<info>Groomer badges and specialties seeded.</info>');
    }

    private function seedTestimonials(OutputInterface $output): void
    {
        if (0 !== $this->testimonialRepository->count([])) {
            $output->writeln('<info>Testimonials already exist, skipping.</info>');

            return;
        }

        $testimonials = [
            ['Anna D.', 'Denver', 'CleanWhiskers made booking easy and stress-free.'],
            ['Marcus L.', 'Seattle', 'Our pup loved the groomer!'],
            ['Priya K.', 'Austin', 'The best service we have tried so far.'],
        ];

        foreach ($testimonials as [$name, $city, $quote]) {
            $this->em->persist(new Testimonial($name, $city, $quote));
        }

        $this->em->flush();
        $output->writeln('<info>Testimonials seeded.</info>');
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
