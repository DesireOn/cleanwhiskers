<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CityRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, CityRepository $cityRepository, ServiceRepository $serviceRepository): Response
    {
        $cities = $cityRepository->findAll();
        $services = $serviceRepository->findAll();

        $citySlug = $request->get('city');
        $serviceSlug = $request->get('service');
        if (is_string($citySlug) && '' !== $citySlug && is_string($serviceSlug) && '' !== $serviceSlug) {
            return $this->redirectToRoute('app_groomer_list_by_city_service', [
                'citySlug' => $citySlug,
                'serviceSlug' => $serviceSlug,
            ]);
        }

        return $this->render('home/index.html.twig', [
            'cities' => $cities,
            'services' => $services,
        ]);
    }
}
