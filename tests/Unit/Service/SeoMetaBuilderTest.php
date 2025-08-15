<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\SeoMetaBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class SeoMetaBuilderTest extends TestCase
{
    private function createBuilder(string $url): SeoMetaBuilder
    {
        $stack = new RequestStack();
        $stack->push(Request::create($url));

        return new SeoMetaBuilder($stack);
    }

    public function testCanonicalOmitsPageOne(): void
    {
        $builder = $this->createBuilder('https://example.com/blog?page=1&foo=bar');
        $seo = $builder->build();

        self::assertSame('https://example.com/blog?foo=bar', $seo['link'][0]['href']);
    }

    public function testCanonicalIncludesPageWhenGreaterThanOne(): void
    {
        $builder = $this->createBuilder('https://example.com/blog?page=2');
        $seo = $builder->build();

        self::assertSame('https://example.com/blog?page=2', $seo['link'][0]['href']);
    }

    public function testCanonicalOverrideWins(): void
    {
        $builder = $this->createBuilder('https://example.com/blog?page=2');
        $seo = $builder->build(['canonical_url' => 'https://override.example/article']);

        self::assertSame('https://override.example/article', $seo['link'][0]['href']);
    }
}
