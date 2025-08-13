<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage', methods: ['GET'])]
    public function index(): Response
    {
        $ctaLinks = [
            'find' => [
                'label' => 'Find a Groomer',
                'url' => '#',
            ],
            'list' => [
                'label' => 'List Your Business',
                'url' => $this->generateUrl('app_register', ['role' => 'groomer']),
            ],
        ];

        return $this->render('home/index.html.twig', [
            'ctaLinks' => $ctaLinks,
        ]);
    }
}
