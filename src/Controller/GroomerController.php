<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use App\Repository\ReviewRepository;
use App\Repository\SeoContentRepository;
use App\Repository\ServiceRepository;
use App\Repository\TestimonialRepository;
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
        TestimonialRepository $testimonialRepository,
        SeoContentRepository $seoContentRepository,
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
        $sort = $request->query->getString('sort', 'recommended');
        $sort = in_array($sort, ['recommended', 'price_asc', 'rating_desc'], true) ? $sort : 'recommended';

        $groomers = $groomerProfileRepository->findByFilters($city, $service, $minRating, $limit, $offset, $sort);

        foreach ($groomers as $groomer) {
            $testimonial = $testimonialRepository->findOneForGroomer($groomer);
            if (null !== $testimonial) {
                /* @phpstan-ignore-next-line */
                $groomer->testimonial = $testimonial;
            }
        }

        $nextOffset = count($groomers) === $limit ? $offset + $limit : null;
        $previousOffset = $offset > 0 ? max(0, $offset - $limit) : null;

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

        $seoContent = $seoContentRepository->findOneByCityAndService($city, $service);

        return $this->render('groomer/list.html.twig', [
            'groomers' => $groomers,
            'city' => $city,
            'service' => $service,
            'rating' => $minRating,
            'sort' => $sort,
            'nextOffset' => $nextOffset,
            'previousOffset' => $previousOffset,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
            'seo_content' => $seoContent,
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
