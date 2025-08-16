<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\Blog\BlogCategoryRepository;
use App\Repository\Blog\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SitemapController extends AbstractController
{
    public function __construct(
        private BlogPostRepository $posts,
        private BlogCategoryRepository $categories,
        private UrlGeneratorInterface $urls,
    ) {
    }

    #[Route('/sitemap.xml', name: 'app_sitemap', methods: ['GET'])]
    public function __invoke(): Response
    {
        $content = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $staticUrls = [
            $this->urls->generate('app_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->urls->generate('app_blog_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
        foreach ($staticUrls as $loc) {
            $content .= sprintf('<url><loc>%s</loc></url>', htmlspecialchars($loc, ENT_XML1));
        }

        foreach ($this->categories->findAll() as $category) {
            $loc = $this->urls->generate('app_blog_category', ['slug' => $category->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
            $content .= sprintf('<url><loc>%s</loc></url>', htmlspecialchars($loc, ENT_XML1));
        }

        foreach ($this->posts->findAllForSitemap() as $post) {
            $loc = $this->urls->generate('app_blog_detail', [
                'year' => $post['publishedAt']->format('Y'),
                'month' => $post['publishedAt']->format('m'),
                'slug' => $post['slug'],
            ], UrlGeneratorInterface::ABSOLUTE_URL);
            $content .= sprintf('<url><loc>%s</loc><lastmod>%s</lastmod></url>', htmlspecialchars($loc, ENT_XML1), $post['updatedAt']->format('Y-m-d'));
        }

        $content .= '</urlset>';

        return new Response($content, Response::HTTP_OK, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
