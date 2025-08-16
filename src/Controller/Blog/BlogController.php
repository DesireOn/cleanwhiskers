<?php

declare(strict_types=1);

namespace App\Controller\Blog;

use App\Repository\Blog\BlogPostRepository;
use App\Repository\Blog\BlogPostSlugHistoryRepository;
use App\Service\SeoMetaBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BlogController extends AbstractController
{
    private const PER_PAGE = 10;
    private const MAX_INDEX_PAGE = 10;

    public function __construct(
        private BlogPostRepository $posts,
        private SeoMetaBuilder $seo,
        private BlogPostSlugHistoryRepository $slugHistory,
    ) {
    }

    public function index(Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $perPage = self::PER_PAGE;
        $posts = $this->posts->findPublished($page, $perPage);

        if ($page > 1 && [] === $posts) {
            throw $this->createNotFoundException();
        }

        $hasNext = count($posts) === $perPage && [] !== $this->posts->findPublished($page + 1, 1);

        $options = [
            'title' => 'Blog – CleanWhiskers',
            'description' => 'Insights and updates from the CleanWhiskers team.',
        ];
        if ($page > 1) {
            $options['prev_url'] = $this->generateUrl('app_blog_index', $page > 2 ? ['page' => $page - 1] : [], UrlGeneratorInterface::ABSOLUTE_URL);
        }
        if ($hasNext) {
            $options['next_url'] = $this->generateUrl('app_blog_index', ['page' => $page + 1], UrlGeneratorInterface::ABSOLUTE_URL);
        }
        if ($page > self::MAX_INDEX_PAGE) {
            $options['robots'] = 'noindex,follow';
        }

        return $this->render('blog/index.html.twig', [
            'title' => 'Blog',
            'posts' => $posts,
            'page' => $page,
            'next_page' => $hasNext ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null,
            'seo' => $this->seo->build($options),
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
            $post = $this->slugHistory->findPublishedPostBySlug($canonicalSlug);
            if (null !== $post) {
                $publishedAt = $post->getPublishedAt();
                if (null === $publishedAt) {
                    throw $this->createNotFoundException();
                }

                return $this->redirectToRoute(
                    'app_blog_detail',
                    [
                        'year' => $publishedAt->format('Y'),
                        'month' => $publishedAt->format('m'),
                        'slug' => $post->getSlug(),
                    ],
                    Response::HTTP_MOVED_PERMANENTLY
                );
            }
            throw $this->createNotFoundException();
        }

        $publishedAt = $post->getPublishedAt();
        if (null === $publishedAt || (int) $publishedAt->format('Y') !== $year || (int) $publishedAt->format('m') !== $month) {
            throw $this->createNotFoundException();
        }

        $options = [
            'title' => $post->getMetaTitle() ?? $post->getTitle().' – CleanWhiskers',
        ];
        if (null !== $post->getMetaDescription()) {
            $options['description'] = $post->getMetaDescription();
        }
        if (null !== $post->getCanonicalUrl()) {
            $options['canonical_url'] = $post->getCanonicalUrl();
        }

        $jsonLd = null;
        if ($post->isPublished()) {
            $jsonLd = [
                'article' => [
                    'headline' => $post->getTitle(),
                    'author' => 'CleanWhiskers',
                    'datePublished' => $post->getPublishedAt()?->format(DATE_ATOM),
                ],
                'breadcrumbs' => [
                    [
                        'name' => 'Home',
                        'item' => $this->generateUrl('app_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    [
                        'name' => 'Blog',
                        'item' => $this->generateUrl('app_blog_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    [
                        'name' => $post->getCategory()->getName(),
                        'item' => $this->generateUrl('app_blog_category', ['slug' => $post->getCategory()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    [
                        'name' => $post->getTitle(),
                        'item' => $this->generateUrl('app_blog_detail', ['year' => $year, 'month' => sprintf('%02d', $month), 'slug' => $post->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                ],
            ];
            if (null !== $post->getCoverImagePath()) {
                $jsonLd['article']['imagePath'] = $post->getCoverImagePath();
            }
        }

        return $this->render('blog/detail.html.twig', [
            'post' => $post,
            'seo' => $this->seo->build($options),
            'jsonld' => $jsonLd,
        ]);
    }
}
