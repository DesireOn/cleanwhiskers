<?php

declare(strict_types=1);

namespace App\Controller\Blog;

use App\Repository\Blog\BlogPostRepository;
use App\Service\SeoMetaBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BlogController extends AbstractController
{
    public function __construct(private BlogPostRepository $posts, private SeoMetaBuilder $seo)
    {
    }

    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $posts = $this->posts->findPublished($page, 10);

        return $this->render('blog/index.html.twig', [
            'title' => 'Blog',
            'posts' => $posts,
            'seo' => $this->seo->build([
                'title' => 'Blog – CleanWhiskers',
                'description' => 'Insights and updates from the CleanWhiskers team.',
            ]),
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
