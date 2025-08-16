<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\Blog\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

final class RssFeedController extends AbstractController
{
    public function __construct(private BlogPostRepository $posts, private Environment $twig)
    {
    }

    #[Route('/feed.xml', name: 'app_rss_feed', methods: ['GET'])]
    public function __invoke(): Response
    {
        $lastBuildDate = new \DateTimeImmutable();
        foreach ($this->posts->findLatestForFeed(1) as $latest) {
            $lastBuildDate = $latest['updatedAt'];
            break;
        }

        $posts = iterator_to_array($this->posts->findLatestForFeed(50));

        $content = $this->twig->render('rss/feed.xml.twig', [
            'posts' => $posts,
            'lastBuildDate' => $lastBuildDate,
        ]);

        return new Response($content, Response::HTTP_OK, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}
