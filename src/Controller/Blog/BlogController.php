<?php

declare(strict_types=1);

namespace App\Controller\Blog;

use App\Repository\Blog\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BlogController extends AbstractController
{
    public function __construct(private BlogPostRepository $posts)
    {
    }

    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $posts = $this->posts->findPublished($page, 10);

        return $this->render('blog/index.html.twig', [
            'title' => 'Blog',
            'posts' => $posts,
            'seo_title' => 'Blog – CleanWhiskers',
            'seo_description' => 'Insights and updates from the CleanWhiskers team.',
        ]);
    }

    public function detail(int $year, int $month, string $slug): Response
    {
        $canonicalSlug = strtolower($slug);
        if ($slug !== $canonicalSlug) {
            return $this->redirectToRoute(
                'app_blog_detail',
                ['year' => $year, 'month' => sprintf('%02d', $month), 'slug' => $canonicalSlug],
                Response::HTTP_MOVED_PERMANENTLY
            );
        }

        $post = $this->posts->findOnePublishedBySlug($canonicalSlug);
        if (null === $post) {
            throw $this->createNotFoundException();
        }

        $publishedAt = $post->getPublishedAt();
        if (null === $publishedAt || (int) $publishedAt->format('Y') !== $year || (int) $publishedAt->format('m') !== $month) {
            throw $this->createNotFoundException();
        }

        return $this->render('blog/detail.html.twig', [
            'post' => $post,
            'seo_title' => $post->getMetaTitle() ?? $post->getTitle().' – CleanWhiskers',
            'seo_description' => $post->getMetaDescription(),
        ]);
    }
}
