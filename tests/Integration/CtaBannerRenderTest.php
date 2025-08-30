<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CtaBannerRenderTest extends WebTestCase
{
    public function testBannerLinksPresent(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertSelectorExists('.get-started');
        self::assertSelectorExists('a.get-started__action[href="#search-form"]');
        self::assertSelectorExists('a.get-started__action[href="/register?role=groomer"]');
    }
}
