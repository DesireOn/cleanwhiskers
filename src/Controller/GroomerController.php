<?php

namespace App\Controller;

use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/groomers')]
class GroomerController extends AbstractController
{
    #[Route('/{slug}', name: 'groomer_show', methods: ['GET'])]
    public function show(GroomerProfileRepository $repo, string $slug): Response
    {
        $groomer = $repo->findOneBy(['slug' => $slug])
            ?? throw $this->createNotFoundException();

        return $this->render('groomer/show.html.twig', compact('groomer'));
    }

    #[Route('/{citySlug}/{serviceSlug}', name: 'groomer_list_city_service', methods: ['GET'])]
    public function list(
        Request $req,
        CityRepository $cities,
        ServiceRepository $services,
        GroomerProfileRepository $groomers,
        string $citySlug,
        string $serviceSlug
    ): Response {
        $city = $cities->findOneBy(['slug' => $citySlug])
            ?? throw $this->createNotFoundException();
        $service = $services->findOneBy(['slug' => $serviceSlug])
            ?? throw $this->createNotFoundException();
        $page = max(1, (int) $req->query->get('page', 1));
        $sort = $req->query->get('sort', 'rating'); // rating|reviews|name

        $pager = $groomers->paginatedByCityAndService($city, $service, $page, $sort);

        return $this->render('groomer/list.html.twig', compact('city', 'service', 'pager', 'sort'));
    }
}
