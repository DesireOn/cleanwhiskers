<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HowItWorksSectionRenderTest extends WebTestCase
{
    public function testSectionHeadingsAndStepsRender(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        self::assertSelectorTextContains('#how-it-works h2', 'How It Works');
        $cards = $crawler->filter('#how-it-works .how-it-works__card');
        self::assertCount(3, $cards);
        self::assertSelectorTextContains('#how-it-works .how-it-works__card:nth-of-type(1) p', 'Browse vetted groomers in your area.');
        self::assertSame('#search-form', $cards->eq(0)->attr('href'));
        self::assertSelectorTextContains('#how-it-works .how-it-works__card:nth-of-type(2) p', 'Choose a time that fits your schedule.');
        self::assertSame('/blog/how-to-book', $cards->eq(1)->attr('href'));
        self::assertSelectorTextContains('#how-it-works .how-it-works__card:nth-of-type(3) p', 'Your pet returns fresh, happy, and healthy.');
        self::assertSame('#featured-groomers', $cards->eq(2)->attr('href'));
    }
}
