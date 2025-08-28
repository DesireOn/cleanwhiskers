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

        if (!\in_array($host, $productionHosts, true)) {
            $content = "User-agent: *\nDisallow: /\n";
        } else {
            $baseDir = $this->getParameter('kernel.project_dir');
            $baseDir = \is_string($baseDir) ? $baseDir : \dirname(__DIR__, 2);
            $path = $baseDir.'/public/robots.txt';

            $content = @file_get_contents($path);
            $content = \is_string($content) ? $content : '';
            $content .= "\nSitemap: ".$request->getScheme().'://'.$host.'/sitemap.xml';
        }

        return new Response($content, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }
}
