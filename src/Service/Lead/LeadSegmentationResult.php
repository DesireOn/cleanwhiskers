<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\GroomerProfile;

/**
 * Result DTO for lead segmentation.
 * Holds matched recipients and excluded candidates with reasons.
 */
class LeadSegmentationResult
{
    /**
     * @var array<int, array{profile: GroomerProfile, email: string, score: float, matches: string[]}>
     */
    private array $recipients = [];

    /**
     * @var array<int, array{profile: GroomerProfile, reason: string}>
     */
    private array $excluded = [];

    /**
     * @return array<int, array{profile: GroomerProfile, email: string, score: float, matches: string[]}>
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * @return array<int, array{profile: GroomerProfile, reason: string}>
     */
    public function getExcluded(): array
    {
        return $this->excluded;
    }

    /**
     * @param string[] $matches
     */
    public function addRecipient(GroomerProfile $profile, string $email, float $score, array $matches = []): void
    {
        $this->recipients[] = [
            'profile' => $profile,
            'email' => $email,
            'score' => $score,
            'matches' => array_values(array_unique($matches)),
        ];
    }

    public function addExclusion(GroomerProfile $profile, string $reason): void
    {
        $this->excluded[] = [
            'profile' => $profile,
            'reason' => $reason,
        ];
    }

    public function sortByScoreDesc(): void
    {
        usort($this->recipients, static function ($a, $b): int {
            $cmp = $b['score'] <=> $a['score'];
            if (0 !== $cmp) {
                return $cmp;
            }

            // Tie-breaker: prefer profiles with a linked user (proxy for verification)
            $aHasUser = null !== $a['profile']->getUser();
            $bHasUser = null !== $b['profile']->getUser();

            if ($aHasUser === $bHasUser) {
                return 0;
            }

            return $bHasUser <=> $aHasUser;
        });
    }
}

