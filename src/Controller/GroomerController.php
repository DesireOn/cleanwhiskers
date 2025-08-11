<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\GroomerProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroomerController extends AbstractController
{
    #[Route('/groomers/{slug}', name: 'app_groomer_show')]
    public function show(string $slug, GroomerProfileRepository $repository): Response
    {
        $groomer = $repository->findOneBySlug($slug);
        if (null === $groomer) {
            throw $this->createNotFoundException();
        }

        return $this->render('groomer/show.html.twig', [
            'groomer' => $groomer,
        ]);
    }
}
