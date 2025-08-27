<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\HomepageController;
use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class HomepageControllerTest extends TestCase
{
    public function testIndexDoesNotQueryServices(): void
    {
        $cityRepository = $this->createMock(CityRepository::class);
        $groomerRepository = $this->createMock(GroomerProfileRepository::class);

        $cityRepository->expects(self::once())
            ->method('findTop')
            ->willReturn([]);
        $groomerRepository->expects(self::once())
            ->method('findFeatured')
            ->with(4)
            ->willReturn([]);

        $controller = $this->getMockBuilder(HomepageController::class)
            ->setConstructorArgs([$cityRepository, $groomerRepository])
            ->onlyMethods(['render', 'generateUrl'])
            ->getMock();

        $controller->expects(self::once())
            ->method('generateUrl')
            ->with('app_register', ['role' => 'groomer'])
            ->willReturn('#');

        $controller->expects(self::once())
            ->method('render')
            ->with(
                'home/index.html.twig',
                self::callback(function (array $context): bool {
                    return !array_key_exists('footerServices', $context)
                        && !array_key_exists('popularServices', $context)
                        && !array_key_exists('services', $context);
                })
            )
            ->willReturn(new Response());

        $response = $controller->index();

        self::assertInstanceOf(Response::class, $response);
    }
}
