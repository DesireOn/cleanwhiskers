<?php

namespace App\Service;

final class FeatureFlags
{
    public function __construct(private bool $featureLeadsEnabled)
    {
    }

    public function isLeadsEnabled(): bool
    {
        return $this->featureLeadsEnabled;
    }
}

