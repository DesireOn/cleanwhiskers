<?php

declare(strict_types=1);

namespace App\Tests\Unit\Routing;

use App\Entity\Service;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;

final class GroomerRouteTest extends KernelTestCase
{
    public function testListByCityServiceRouteGeneration(): void
    {
        self::bootKernel();
        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');

        $url = $router->generate('app_groomer_list_by_city_service', [
            'citySlug' => 'sofia',
            'serviceSlug' => Service::MOBILE_DOG_GROOMING,
        ]);

        self::assertSame('/groomers/sofia/mobile-dog-grooming', $url);
    }
}
