<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RobotsController extends AbstractController
{
    #[Route('/robots.txt', name: 'app_robots', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $host = $request->getHost();
        $productionHosts = ['cleanwhiskers.com', 'www.cleanwhiskers.com'];

        $content = "User-agent: *\nDisallow: /\n";

        return new Response($content, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }
}
