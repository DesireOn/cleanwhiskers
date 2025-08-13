<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CityRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class SearchController extends AbstractController
{
    public function __construct(
        private readonly CityRepository $cityRepository,
        private readonly ServiceRepository $serviceRepository,
    ) {
    }

    #[Route('/search', name: 'app_search_redirect', methods: ['GET'])]
    public function redirectAction(Request $request): Response
    {
        $citySlug = (string) $request->query->get('city', '');
        if ('' === $citySlug) {
            return $this->redirectToRoute('app_homepage');
        }

        $city = $this->cityRepository->findOneBySlug($citySlug);
        if (null === $city) {
            $this->addFlash('error', 'City not found.');

            return $this->redirectToRoute('app_homepage');
        }

        $serviceSlug = (string) $request->query->get('service', '');
        if ('' !== $serviceSlug) {
            $service = $this->serviceRepository->findOneBySlug($serviceSlug);
            if (null !== $service) {
                return $this->redirectToRoute('app_groomer_list_by_city_service', [
                    'citySlug' => $city->getSlug(),
                    'serviceSlug' => $service->getSlug(),
                ]);
            }
        }

        return $this->redirectToRoute('app_groomer_list_by_city', [
            'citySlug' => $city->getSlug(),
        ]);
    }
}
