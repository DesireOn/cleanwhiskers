<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class BlogController extends AbstractController
{
    #[Route('/blog', name: 'app_blog_index', methods: ['GET'])]
    public function index(): Response
    {
        $posts = $this->getPosts();

        return $this->render('blog/index.html.twig', [
            'posts' => $posts,
            'seo_title' => 'Blog – CleanWhiskers',
            'seo_description' => 'Insights and updates from the CleanWhiskers team.',
        ]);
    }

    #[Route('/blog/{slug}', name: 'app_blog_show', methods: ['GET'])]
    public function show(string $slug): Response
    {
        $posts = $this->getPosts();
        $post = $posts[$slug] ?? null;
        if (null === $post) {
            throw $this->createNotFoundException();
        }

        return $this->render('blog/show.html.twig', [
            'post' => $post,
            'seo_title' => $post['seo_title'],
            'seo_description' => $post['seo_description'],
        ]);
    }

    /**
     * @return array<string, array{title: string, excerpt: string, template: string, seo_title: string, seo_description: string}>
     */
    private function getPosts(): array
    {
        return [
            'welcome-to-cleanwhiskers' => [
                'title' => 'Welcome to CleanWhiskers',
                'excerpt' => 'Discover tips for grooming your beloved pets.',
                'template' => 'blog/posts/welcome.html.twig',
                'seo_title' => 'Welcome to CleanWhiskers Blog – CleanWhiskers',
                'seo_description' => 'Learn about our mission and how we help pet owners.',
            ],
            'healthy-grooming-habits' => [
                'title' => 'Healthy Grooming Habits',
                'excerpt' => 'Simple routines to keep your pet feeling great.',
                'template' => 'blog/posts/grooming-habits.html.twig',
                'seo_title' => 'Healthy Grooming Habits for Pets – CleanWhiskers',
                'seo_description' => 'Establish routines for a clean, happy pet.',
            ],
        ];
    }
}
