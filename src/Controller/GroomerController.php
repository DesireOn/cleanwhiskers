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

    #[Route('/groomers/{slug}', name: 'app_groomer_show', methods: ['GET'])]
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
}
