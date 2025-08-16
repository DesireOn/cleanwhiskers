<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\Blog\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
        $lastPost = iterator_to_array($this->posts->findLatestForFeed(1));
        $lastBuildDate = $lastPost[0]['updatedAt'] ?? new \DateTimeImmutable();
        $posts = $this->posts->findLatestForFeed(50);

        $response = new StreamedResponse(function () use ($posts, $lastBuildDate): void {
            $this->twig->display('rss/feed.xml.twig', [
                'posts' => $posts,
                'lastBuildDate' => $lastBuildDate,
            ]);
        });

        $response->headers->set('Content-Type', 'application/rss+xml; charset=UTF-8');

        return $response;
    }
}
