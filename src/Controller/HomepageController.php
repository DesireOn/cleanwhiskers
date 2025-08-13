<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CityRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    public function __construct(
        private readonly CityRepository $cityRepository,
        private readonly ServiceRepository $serviceRepository,
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

        $footerCities = $this->cityRepository->findBy([], ['name' => 'ASC'], 5);
        $footerServices = $this->serviceRepository->findBy([], ['name' => 'ASC'], 5);

        return $this->render('home/index.html.twig', [
            'ctaLinks' => $ctaLinks,
            'footerCities' => $footerCities,
            'footerServices' => $footerServices,
        ]);
    }
}
