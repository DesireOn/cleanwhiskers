<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    public function __construct(
        private readonly CityRepository $cityRepository,
        private readonly GroomerProfileRepository $groomerProfileRepository,
    ) {
    }

    #[Route('/', name: 'app_homepage', methods: ['GET'])]
    public function index(): Response
    {
        $ctaLinks = [
            'find' => [
                'label' => 'Find a Groomer',
                'url' => '#',
            ],
            'list' => [
                'label' => 'List Your Business',
                'url' => $this->generateUrl('app_register', ['role' => 'groomer']),
            ],
        ];

        $featuredGroomers = $this->groomerProfileRepository->findFeatured(4);

        $cities = $this->cityRepository->findTop();
        $footerCities = $cities;
        $popularCities = $cities;

        return $this->render('home/index.html.twig', [
            'ctaLinks' => $ctaLinks,
            'footerCities' => $footerCities,
            'popularCities' => $popularCities,
            'featuredGroomers' => $featuredGroomers,
            'seo_title' => 'Book local pet care | CleanWhiskers',
            'seo_description' => 'Discover trusted groomers and pet boarding near you. CleanWhiskers makes booking easy.',
        ]);
    }
}
