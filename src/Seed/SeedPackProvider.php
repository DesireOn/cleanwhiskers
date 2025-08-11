<?php

declare(strict_types=1);

namespace App\Seed;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Provides access to configured seed packs.
 */
final class SeedPackProvider
{
    public function __construct(private ParameterBagInterface $parameters)
    {
    }

    /**
     * @return array{entities: array<int, string>, withSamples: bool}
     */
    public function get(string $name): array
    {
        /** @var array<string, array{entities: array<int, string>, withSamples: bool}> $packs */
        $packs = $this->parameters->get('seed.packs');

        if (!isset($packs[$name])) {
            throw new \InvalidArgumentException(sprintf('Seed pack "%s" is not defined.', $name));
        }

        return $packs[$name];
    }
}
