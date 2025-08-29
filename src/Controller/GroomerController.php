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
        ReviewRepository $reviewRepository,
    ): Response {
        $city = $cityRepository->findOneBySlug($citySlug);
        if (null === $city) {
            throw $this->createNotFoundException();
        }

        $service = $serviceRepository->findOneBySlug($serviceSlug);
        if (null === $service) {
            throw $this->createNotFoundException();
        }
        $perPage = 12;
        $page = max(1, $request->query->getInt('page', 1));
        $sort = (string) $request->query->get('sort', 'recommended');
        $allowed = ['recommended', 'price_asc', 'rating_desc'];
        if (!in_array($sort, $allowed, true)) {
            $sort = 'recommended';
        }

        $paginator = $groomerProfileRepository->findByFilters($city, $service, $sort, $page, $perPage);
        $total = count($paginator);
        $totalPages = (int) ceil($total / $perPage);
        $hasPrev = $page > 1;
        $hasNext = $page < $totalPages;

        $seoTitle = sprintf(
            'Mobile Dog Groomers in %s for %s – CleanWhiskers',
            $city->getName(),
            $service->getName()
        );
        $seoDescription = $this->truncate(
            sprintf(
                'Find mobile dog groomers in %s for %s. Book experienced groomers on CleanWhiskers.',
                $city->getName(),
                $service->getName()
            )
        );

        $groomers = iterator_to_array($paginator->getIterator());
        $ids = array_map(static fn($g) => $g->getId(), $groomers);
        $ratingMap = [];
        $countMap = [];
        if ($ids) {
            $stats = $reviewRepository->getAveragesForGroomers($ids);
            foreach ($ids as $id) {
                $ratingMap[$id] = $stats[$id]['avg'] ?? null;
                $countMap[$id] = $stats[$id]['count'] ?? 0;
            }
        }

        return $this->render('groomer/list.html.twig', [
            'groomers' => $groomers,
            'city' => $city,
            'service' => $service,
            'sort' => $sort,
            'page' => $page,
            'total_pages' => $totalPages,
            'has_prev' => $hasPrev,
            'has_next' => $hasNext,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
            'ratings' => $ratingMap,
            'reviewCounts' => $countMap,
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

    private function truncate(string $text, int $max = 160): string
    {
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max - 3).'...' : $text;
    }
}
