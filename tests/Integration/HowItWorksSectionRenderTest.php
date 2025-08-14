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

        self::assertSelectorTextContains('#how-it-works .owner-steps h3', 'For Owners');
        self::assertSelectorCount(3, '#how-it-works .owner-steps li');
        self::assertSelectorTextContains('#how-it-works .groomer-steps h3', 'For Groomers');
        self::assertSelectorCount(3, '#how-it-works .groomer-steps li');
    }
}
