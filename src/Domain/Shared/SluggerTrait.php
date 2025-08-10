<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use App\Domain\Shared\Exception\InvalidSlugSourceException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;

trait SluggerTrait
{
    #[ORM\Column(length: 255, unique: true)]
    private string $slug = '';

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function refreshSlugFrom(string $source): void
    {
        $normalized = preg_replace('/\s+/', ' ', mb_strtolower(trim($source)));
        if (null === $normalized || '' === $normalized) {
            throw new InvalidSlugSourceException('Slug source cannot be empty.');
        }

        $slug = (new AsciiSlugger())->slug($normalized)->lower()->toString();
        if ('' === $slug) {
            throw new InvalidSlugSourceException('Slug cannot be empty.');
        }

        $this->slug = $slug;
    }
}
