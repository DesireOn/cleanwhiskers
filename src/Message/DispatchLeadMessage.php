<?php

declare(strict_types=1);

namespace App\Message;

final class DispatchLeadMessage
{
    public function __construct(public readonly int $leadId)
    {
    }

    public function getLeadId(): int
    {
        return $this->leadId;
    }
}

