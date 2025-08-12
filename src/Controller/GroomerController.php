<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GroomerController extends AbstractController
{
    #[Route('/groomers/{citySlug}/{serviceSlug}', name: 'app_groomer_list_by_city_service', methods: ['GET'])]
    public function listByCityService(
        string $citySlug,
        string $serviceSlug,
        Request $request,
        CityRepository $cityRepository,
        ServiceRepository $serviceRepository,
        GroomerProfileRepository $groomerProfileRepository,
    ): Response {
        $city = $cityRepository->findOneBySlug($citySlug);
        if (null === $city) {
            throw $this->createNotFoundException();
        }

        $service = $serviceRepository->findOneBySlug($serviceSlug);
        if (null === $service) {
            throw $this->createNotFoundException();
        }

        $offset = max(0, $request->query->getInt('offset', 0));
        $groomers = $groomerProfileRepository->findByCityAndService($city, $service, 20, $offset);

        return $this->render('groomer/list.html.twig', [
            'groomers' => $groomers,
            'city' => $city,
            'service' => $service,
        ]);
    }

    #[Route(
        '/groomers/{citySlug}',
        name: 'app_groomer_list_by_city',
        methods: ['GET'],
        requirements: ['citySlug' => '[a-z0-9]+']
    )]
    public function listByCity(
        string $citySlug,
        Request $request,
        CityRepository $cityRepository,
        GroomerProfileRepository $groomerProfileRepository,
    ): Response {
        $city = $cityRepository->findOneBySlug($citySlug);
        if (null === $city) {
            throw $this->createNotFoundException();
        }

        $page = max(1, $request->query->getInt('page', 1));
        $groomers = $groomerProfileRepository->findByCitySlug($citySlug, $page);

        return $this->render('groomer/list_by_city.html.twig', [
            'groomers' => $groomers,
            'city' => $city,
        ]);
    }

    #[Route('/groomers/{slug}', name: 'app_groomer_show', methods: ['GET'], requirements: ['slug' => '[^/]+-[^/]+'])]
    public function show(string $slug, GroomerProfileRepository $groomerProfileRepository): Response
    {
        $groomer = $groomerProfileRepository->findOneBySlug($slug);
        if (null === $groomer) {
            throw $this->createNotFoundException();
        }

        return $this->render('groomer/show.html.twig', [
            'groomer' => $groomer,
        ]);
    }

    #[Route('/groomers/{slug}/', name: 'app_groomer_show_trailing', methods: ['GET'], requirements: ['slug' => '[^/]+-[^/]+'])]
    public function showTrailingSlash(string $slug): Response
    {
        return $this->redirectToRoute('app_groomer_show', ['slug' => $slug], Response::HTTP_MOVED_PERMANENTLY);
    }
}
