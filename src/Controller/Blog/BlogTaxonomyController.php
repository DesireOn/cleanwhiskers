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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BlogTaxonomyController extends AbstractController
{
    private const PER_PAGE = 10;
    private const MAX_INDEX_PAGE = 10;

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

        $page = max(1, $request->query->getInt('page', 1));
        $perPage = self::PER_PAGE;
        $posts = $this->posts->findByCategorySlug($canonicalSlug, $page, $perPage);
        $latest = null;
        foreach ($posts as $post) {
            if (isset($post['updatedAt']) && (null === $latest || $post['updatedAt'] > $latest)) {
                $latest = $post['updatedAt'];
            }
        }
        if ($latest instanceof \DateTimeImmutable) {
            $request->attributes->set('updated_at', $latest);
        }

        if ($page > 1 && [] === $posts) {
            throw $this->createNotFoundException();
        }

        $hasNext = count($posts) === $perPage && [] !== $this->posts->findByCategorySlug($canonicalSlug, $page + 1, 1);

        $options = [
            'title' => $category->getName().' – CleanWhiskers',
        ];
        if ($page > 1) {
            $options['prev_url'] = $this->generateUrl('app_blog_category', ['slug' => $canonicalSlug] + ($page > 2 ? ['page' => $page - 1] : []), UrlGeneratorInterface::ABSOLUTE_URL);
        }
        if ($hasNext) {
            $options['next_url'] = $this->generateUrl('app_blog_category', ['slug' => $canonicalSlug, 'page' => $page + 1], UrlGeneratorInterface::ABSOLUTE_URL);
        }
        if ($page > self::MAX_INDEX_PAGE) {
            $options['robots'] = 'noindex,follow';
        }

        return $this->render('blog/category.html.twig', [
            'title' => $category->getName(),
            'posts' => $posts,
            'page' => $page,
            'next_page' => $hasNext ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null,
            'seo' => $this->seo->build($options),
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

        $page = max(1, $request->query->getInt('page', 1));
        $perPage = self::PER_PAGE;
        $posts = $this->posts->findByTagSlug($canonicalSlug, $page, $perPage);
        $latest = null;
        foreach ($posts as $post) {
            if (isset($post['updatedAt']) && (null === $latest || $post['updatedAt'] > $latest)) {
                $latest = $post['updatedAt'];
            }
        }
        if ($latest instanceof \DateTimeImmutable) {
            $request->attributes->set('updated_at', $latest);
        }

        if ($page > 1 && [] === $posts) {
            throw $this->createNotFoundException();
        }

        $hasNext = count($posts) === $perPage && [] !== $this->posts->findByTagSlug($canonicalSlug, $page + 1, 1);

        $options = [
            'title' => $tag->getName().' – CleanWhiskers',
        ];
        if ($page > 1) {
            $options['prev_url'] = $this->generateUrl('app_blog_tag', ['slug' => $canonicalSlug] + ($page > 2 ? ['page' => $page - 1] : []), UrlGeneratorInterface::ABSOLUTE_URL);
        }
        if ($hasNext) {
            $options['next_url'] = $this->generateUrl('app_blog_tag', ['slug' => $canonicalSlug, 'page' => $page + 1], UrlGeneratorInterface::ABSOLUTE_URL);
        }
        if ($page > self::MAX_INDEX_PAGE) {
            $options['robots'] = 'noindex,follow';
        }

        return $this->render('blog/tag.html.twig', [
            'title' => $tag->getName(),
            'posts' => $posts,
            'page' => $page,
            'next_page' => $hasNext ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null,
            'seo' => $this->seo->build($options),
        ]);
    }
}
