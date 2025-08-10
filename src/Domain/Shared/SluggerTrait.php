<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Symfony\Component\String\Slugger\AsciiSlugger;

trait SluggerTrait
{
    public function refreshSlugFrom(string $source): void
    {
        $this->slug = (new AsciiSlugger())->slug($source)->lower()->toString();
    }
}
