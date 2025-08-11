<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CityController extends AbstractController
{
    public function __construct(private readonly CityRepository $cityRepository)
    {
    }

    #[Route('/cities/{slug}', name: 'app_city_show')]
    public function show(string $slug): Response
    {
        $city = $this->cityRepository->findOneBySlug($slug);
        if (null === $city) {
            throw $this->createNotFoundException();
        }

        return $this->render('city/show.html.twig', [
            'city' => $city,
        ]);
    }
}
