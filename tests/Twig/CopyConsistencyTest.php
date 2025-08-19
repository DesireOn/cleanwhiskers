<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

final class CopyConsistencyTest extends KernelTestCase
{
    public function testCopyIsConsistentAcrossTemplates(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $twig = $container->get('twig');

        // Header
        $requestStack = $container->get('request_stack');
        $request = new Request();
        $request->attributes->set('_route', 'app_homepage');
        $requestStack->push($request);
        $header = $twig->render('partials/_header.html.twig');
        self::assertStringContainsString('Find a Groomer', $header);
        self::assertStringContainsString('List Your Business', $header);
        self::assertStringNotContainsString('Join as Groomer', $header);

        // Hero
        $hero = $twig->render('home/partials/_hero.html.twig', [
            'ctaLinks' => [
                'find' => ['label' => 'Find a Groomer'],
                'list' => ['label' => 'List Your Business', 'url' => '/register'],
            ],
            'cities' => [
                ['slug' => 'sofia', 'name' => 'Sofia'],
            ],
            'services' => [
                ['slug' => 'grooming', 'name' => 'Grooming'],
            ],
        ]);
        self::assertStringContainsString('Book mobile pet grooming in minutes — trusted local professionals.', $hero);
        self::assertStringContainsString('List Your Business', $hero);
        self::assertStringNotContainsString('Join as Groomer', $hero);

        // CTA banner
        $banner = $twig->render('home/partials/_cta_banner.html.twig');
        self::assertStringContainsString('Get Started', $banner);
        self::assertStringContainsString('Find a Groomer', $banner);
        self::assertStringContainsString('List Your Business', $banner);

        // Footer
        $footer = $twig->render('partials/_footer.html.twig');
        self::assertStringContainsString('Find trusted pet groomers near you.', $footer);
    }
}
