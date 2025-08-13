<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

final class JsonLdWebsiteTest extends KernelTestCase
{
    private Environment $twig;
    private RequestStack $requestStack;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->twig = $container->get(Environment::class);
        $this->requestStack = $container->get(RequestStack::class);
    }

    public function testJsonLdStructure(): void
    {
        $this->requestStack->push(Request::create('/'));
        $html = $this->twig->render('base.html.twig');
        $this->requestStack->pop();

        self::assertMatchesRegularExpression('/<script type="application\\/ld\+json">(.*?)<\\/script>/s', $html, $matches);
        $data = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('WebSite', $data['@type']);
        self::assertSame('http://localhost/', $data['url']);
        self::assertSame('SearchAction', $data['potentialAction']['@type']);
        self::assertStringContainsString('/groomers/{city}/{service}', $data['potentialAction']['target']);
        self::assertSame([
            'required name=city',
            'required name=service',
        ], $data['potentialAction']['query-input']);
    }
}
