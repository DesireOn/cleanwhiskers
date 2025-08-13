<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CityRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/autocomplete', name: 'app_autocomplete_')]
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
        $q = trim(mb_strtolower($request->query->getString('q')));
        if (mb_strlen($q) < 2) {
            return $this->json([]);
        }

        return $this->json($this->cityRepository->findByNameLike($q));
    }

    #[Route('/services', name: 'services', methods: ['GET'])]
    public function services(Request $request): JsonResponse
    {
        $q = trim(mb_strtolower($request->query->getString('q')));
        if (mb_strlen($q) < 2) {
            return $this->json([]);
        }

        return $this->json($this->serviceRepository->findByNameLike($q));
    }
}
