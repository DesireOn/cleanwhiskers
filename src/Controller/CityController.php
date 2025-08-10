<?php

namespace App\Controller;

use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cities')]
class CityController extends AbstractController
{
    #[Route('/{slug}', name: 'city_show', methods: ['GET'])]
    public function show(CityRepository $repo, string $slug): Response
    {
        $city = $repo->findOneBy(['slug' => $slug])
            ?? throw $this->createNotFoundException();

        return $this->render('city/show.html.twig', compact('city'));
    }
}
