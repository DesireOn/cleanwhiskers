<?php

declare(strict_types=1);

namespace App\Tests\Unit\Routing;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class GroomerRouteGenerationTest extends KernelTestCase
{
    public function testListByCityServiceRouteGeneratesExpectedPath(): void
    {
        self::bootKernel();
        $router = self::getContainer()->get(UrlGeneratorInterface::class);

        $url = $router->generate('app_groomer_list_by_city_service', [
            'citySlug' => 'sofia',
            'serviceSlug' => 'mobile-dog-grooming',
        ]);

        self::assertSame('/groomers/sofia/mobile-dog-grooming', $url);
    }
}
