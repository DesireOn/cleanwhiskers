<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CityRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/autocomplete', name: 'api_autocomplete_')]
final class AutocompleteController extends AbstractController
{
    public function __construct(
        private readonly CityRepository $cityRepository,
        private readonly ServiceRepository $serviceRepository,
    ) {
    }

    #[Route('/cities', name: 'cities', methods: ['GET'])]
    public function cities(Request $request): JsonResponse
    {
        $q = preg_replace('/[^\p{L}\s-]/u', '', trim((string) $request->query->get('q', ''))) ?? '';
        if (mb_strlen($q) < 2) {
            return $this->json([]);
        }

        $cities = $this->cityRepository->findByNameLike($q, 8);

        return $this->json(array_map(
            static fn ($city) => ['name' => $city->getName(), 'slug' => $city->getSlug()],
            $cities
        ));
    }

    #[Route('/services', name: 'services', methods: ['GET'])]
    public function services(Request $request): JsonResponse
    {
        $q = preg_replace('/[^\p{L}\s-]/u', '', trim((string) $request->query->get('q', ''))) ?? '';
        if (mb_strlen($q) < 2) {
            return $this->json([]);
        }

        $services = $this->serviceRepository->findByNameLike($q, 8);

        return $this->json(array_map(
            static fn ($service) => ['name' => $service->getName(), 'slug' => $service->getSlug()],
            $services
        ));
    }
}
