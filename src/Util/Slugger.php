<?php

namespace App\Util;

use Symfony\Component\String\Slugger\AsciiSlugger;

class Slugger
{
    private AsciiSlugger $slugger;

    public function __construct()
    {
        $this->slugger = new AsciiSlugger();
    }

    public function slugify(string $string): string
    {
        return $this->slugger->slug($string)->lower()->toString();
    }
}
