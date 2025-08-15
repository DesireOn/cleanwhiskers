<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

final class HeaderTemplateTest extends KernelTestCase
{
    public function testHeaderHasPrimaryLinks(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $requestStack = $container->get('request_stack');
        $request = new Request();
        $request->attributes->set('_route', 'app_homepage');
        $requestStack->push($request);

        $twig = $container->get('twig');
        $html = $twig->render('partials/_header.html.twig');

        self::assertStringContainsString('href="/search"', $html);
        self::assertStringContainsString('href="/register?role=groomer"', $html);
        self::assertStringContainsString('href="/blog"', $html);
        self::assertStringNotContainsString('#about', $html);
    }
}
