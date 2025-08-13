<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Entity\City;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class SeoHeadPartialTest extends KernelTestCase
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

    public function testHomepageSeoTags(): void
    {
        $this->requestStack->push(Request::create('/'));
        $html = $this->twig->render('homepage/index.html.twig');
        $this->requestStack->pop();

        self::assertStringContainsString('<link rel="canonical" href="http://localhost/">', $html);
        self::assertMatchesRegularExpression('/<meta property="og:title" content="[^\"]+"/', $html);
        self::assertMatchesRegularExpression('/<meta property="og:description" content="[^\"]+"/', $html);
    }

    public function testCityPageSeoTags(): void
    {
        $city = new City('Sofia');
        $city->setSeoIntro('Great grooming in Sofia.');

        $this->requestStack->push(Request::create('/cities/sofia'));
        $html = $this->twig->render('city/show.html.twig', ['city' => $city]);
        $this->requestStack->pop();

        self::assertStringContainsString('<link rel="canonical" href="http://localhost/cities/sofia">', $html);
        self::assertMatchesRegularExpression('/<meta property="og:title" content="Sofia"/', $html);
        self::assertMatchesRegularExpression('/<meta property="og:description" content="[^\"]+"/', $html);
    }
}
