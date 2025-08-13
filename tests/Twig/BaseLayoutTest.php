<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

class BaseLayoutTest extends KernelTestCase
{
    public function testBaseTemplateRenders(): void
    {
        self::bootKernel();
        $twig = static::getContainer()->get(Environment::class);
        $html = $twig->render('base.html.twig');
        self::assertStringContainsString('<main', $html);
    }
}
