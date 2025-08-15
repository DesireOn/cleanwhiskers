<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Builds SEO metadata arrays for templates.
 */
final class SeoMetaBuilder
{
    private const DEFAULT_TITLE = 'Find trusted pet care | CleanWhiskers';
    private const DEFAULT_DESCRIPTION = 'Book top-rated grooming and boarding services near you with CleanWhiskers.';
    private const DEFAULT_IMAGE = 'https://www.cleanwhiskers.example/social-card.png';

    public function __construct(private RequestStack $requestStack)
    {
    }

    /**
     * @param array{title?: string, description?: string, image?: string, canonical_url?: string} $options
     *
     * @return array{
     *     title: string,
     *     meta: list<array{name?: string, property?: string, content: string}>,
     *     link: list<array{rel: string, href: string}>
     * }
     */
    public function build(array $options = []): array
    {
        $title = $options['title'] ?? self::DEFAULT_TITLE;
        $description = $options['description'] ?? self::DEFAULT_DESCRIPTION;
        $image = $options['image'] ?? self::DEFAULT_IMAGE;
        $canonical = $options['canonical_url'] ?? $this->deriveCanonical();

        return [
            'title' => $title,
            'meta' => [
                ['name' => 'description', 'content' => $description],
                ['property' => 'og:title', 'content' => $title],
                ['property' => 'og:description', 'content' => $description],
                ['property' => 'og:type', 'content' => 'article'],
                ['property' => 'og:image', 'content' => $image],
                ['name' => 'twitter:card', 'content' => 'summary_large_image'],
                ['name' => 'twitter:title', 'content' => $title],
                ['name' => 'twitter:description', 'content' => $description],
                ['name' => 'twitter:image', 'content' => $image],
            ],
            'link' => [
                ['rel' => 'canonical', 'href' => $canonical],
            ],
        ];
    }

    private function deriveCanonical(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return '';
        }

        $query = $request->query->all();
        $page = $request->query->getInt('page', 1);
        if ($page <= 1) {
            unset($query['page']);
        } else {
            $query['page'] = $page;
        }

        $uri = $request->getSchemeAndHttpHost().$request->getBaseUrl().$request->getPathInfo();
        if ([] !== $query) {
            return $uri.'?'.http_build_query($query);
        }

        return $uri;
    }
}
