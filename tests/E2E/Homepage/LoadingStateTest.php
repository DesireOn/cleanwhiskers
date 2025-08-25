<?php

declare(strict_types=1);

namespace App\Tests\E2E\Homepage;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LoadingStateTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testSearchButtonsDoNotIncludeSpinner(): void
    {
        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $spinners = $crawler->filter('.search-form__button .spinner');
        self::assertCount(0, $spinners);
    }
}
