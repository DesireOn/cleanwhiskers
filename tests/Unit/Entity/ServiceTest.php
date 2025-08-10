<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Service;
use PHPUnit\Framework\TestCase;

final class ServiceTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $service = new Service();
        $service->setName('Bath');
        $service->setSlug('bath');

        self::assertSame('Bath', $service->getName());
        self::assertSame('bath', $service->getSlug());
    }
}
