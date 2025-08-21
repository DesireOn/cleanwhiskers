<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class HeroTemplateTest extends KernelTestCase
{
    public function testHeroContainsCopyAndFields(): void
    {
        self::bootKernel();
        $twig = self::getContainer()->get('twig');

        $html = $twig->render('home/partials/_hero.html.twig', [
            'ctaLinks' => [
                'find' => ['label' => 'Find a Groomer'],
            ],
            'cities' => [
                ['slug' => 'sofia', 'name' => 'Sofia'],
            ],
        ]);

        self::assertStringContainsString('Book mobile pet grooming in minutes — trusted local professionals.', $html);
        self::assertStringContainsString('name="city"', $html);
        self::assertStringNotContainsString('name="service"', $html);
    }
}
