<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HowItWorksSectionRenderTest extends WebTestCase
{
    public function testSectionHeadingsAndStepsRender(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        self::assertSelectorTextContains('#how-it-works h2', 'How It Works');
        self::assertSelectorCount(3, '#how-it-works .how-it-works__card');
    }
}
