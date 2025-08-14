<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use App\Repository\ReviewRepository;
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
        $limit = 20;
        $offset = max(0, $request->query->getInt('offset', 0));
        $minRating = $request->query->getInt('rating');
        $minRating = $minRating > 0 ? $minRating : null;

        $groomers = $groomerProfileRepository->findByFilters($city, $service, $minRating, $limit, $offset);

        $nextOffset = count($groomers) === $limit ? $offset + $limit : null;
        $previousOffset = $offset > 0 ? max(0, $offset - $limit) : null;

        return $this->render('groomer/list.html.twig', [
            'groomers' => $groomers,
            'city' => $city,
            'service' => $service,
            'rating' => $minRating,
            'nextOffset' => $nextOffset,
            'previousOffset' => $previousOffset,
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
    public function profile(string $slug, GroomerProfileRepository $groomerProfileRepository, ReviewRepository $reviewRepository): Response
    {
        $groomer = $groomerProfileRepository->findOneBySlug($slug);
        if (null === $groomer) {
            throw $this->createNotFoundException();
        }

        $reviews = $reviewRepository->findByGroomerProfileOrderedByDate($groomer);

        return $this->render('groomer/profile.html.twig', [
            'groomer' => $groomer,
            'reviews' => $reviews,
        ]);
    }

    #[Route('/groomers/{slug}/', name: 'app_groomer_show_trailing', methods: ['GET'], requirements: ['slug' => '[^/]+-[^/]+'])]
    public function profileTrailingSlash(string $slug): Response
    {
        return $this->redirectToRoute('app_groomer_show', ['slug' => $slug], Response::HTTP_MOVED_PERMANENTLY);
    }
}
