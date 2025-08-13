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

        self::assertSelectorExists('.cta-banner');
        self::assertSelectorExists('a.cta-banner__link--owners[href="#search-form"]');
        self::assertSelectorExists('a.cta-banner__link--groomers[href="/register?role=groomer"]');
    }
}
