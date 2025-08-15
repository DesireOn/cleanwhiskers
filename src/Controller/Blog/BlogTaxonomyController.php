<?php

declare(strict_types=1);

namespace App\Controller\Blog;

use App\Repository\Blog\BlogCategoryRepository;
use App\Repository\Blog\BlogPostRepository;
use App\Repository\Blog\BlogTagRepository;
use App\Service\SeoMetaBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BlogTaxonomyController extends AbstractController
{
    public function __construct(
        private BlogPostRepository $posts,
        private BlogCategoryRepository $categories,
        private BlogTagRepository $tags,
        private SeoMetaBuilder $seo,
    ) {
    }

    public function category(Request $request, string $slug): Response
    {
        $canonicalSlug = strtolower($slug);
        if ($slug !== $canonicalSlug) {
            return $this->redirectToRoute(
                'app_blog_category',
                ['slug' => $canonicalSlug],
                Response::HTTP_MOVED_PERMANENTLY
            );
        }

        $category = $this->categories->findOneBy(['slug' => $canonicalSlug]);
        if (null === $category) {
            throw $this->createNotFoundException();
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $posts = $this->posts->findByCategorySlug($canonicalSlug, $page, 10);

        return $this->render('blog/category.html.twig', [
            'title' => $category->getName(),
            'posts' => $posts,
            'seo' => $this->seo->build([
                'title' => $category->getName().' – CleanWhiskers',
            ]),
        ]);
    }

    public function tag(Request $request, string $slug): Response
    {
        $canonicalSlug = strtolower($slug);
        if ($slug !== $canonicalSlug) {
            return $this->redirectToRoute(
                'app_blog_tag',
                ['slug' => $canonicalSlug],
                Response::HTTP_MOVED_PERMANENTLY
            );
        }

        $tag = $this->tags->findOneBy(['slug' => $canonicalSlug]);
        if (null === $tag) {
            throw $this->createNotFoundException();
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $posts = $this->posts->findByTagSlug($canonicalSlug, $page, 10);

        return $this->render('blog/tag.html.twig', [
            'title' => $tag->getName(),
            'posts' => $posts,
            'seo' => $this->seo->build([
                'title' => $tag->getName().' – CleanWhiskers',
            ]),
        ]);
    }
}
