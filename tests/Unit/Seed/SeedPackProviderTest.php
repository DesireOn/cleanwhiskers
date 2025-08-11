<?php

declare(strict_types=1);

namespace App\Tests\Unit\Seed;

use App\Seed\SeedPackProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class SeedPackProviderTest extends TestCase
{
    public function testReturnsPackConfig(): void
    {
        $bag = new ParameterBag([
            'seed.packs' => [
                'staging' => [
                    'entities' => ['default'],
                    'withSamples' => true,
                ],
                'production' => [
                    'entities' => ['default'],
                    'withSamples' => false,
                ],
            ],
        ]);
        $provider = new SeedPackProvider($bag);

        $pack = $provider->get('staging');

        self::assertSame(
            ['entities' => ['default'], 'withSamples' => true],
            $pack
        );
    }

    public function testThrowsWhenPackMissing(): void
    {
        $bag = new ParameterBag(['seed.packs' => []]);
        $provider = new SeedPackProvider($bag);

        $this->expectException(\InvalidArgumentException::class);
        $provider->get('unknown');
    }
}
