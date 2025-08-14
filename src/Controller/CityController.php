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

        $seoTitle = sprintf('Mobile Dog Groomers in %s – CleanWhiskers', $city->getName());
        $seoDescription = $this->truncate(
            sprintf('Explore top mobile dog groomers in %s. Book trusted pros on CleanWhiskers.', $city->getName())
        );

        return $this->render('city/show.html.twig', [
            'city' => $city,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
        ]);
    }

    private function truncate(string $text, int $max = 160): string
    {
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max - 3).'...' : $text;
    }
}
