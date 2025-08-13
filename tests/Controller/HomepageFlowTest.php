<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomepageFlowTest extends WebTestCase
{
    public function testSearchFormHasAutocompleteFields(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('id="city-input"', $content);
        self::assertStringContainsString('id="service-input"', $content);
        self::assertStringContainsString('js/autocomplete.js', $content);
    }
}
