<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Repository\EmailSuppressionRepository;
use App\Repository\GroomerProfileRepository;

class LeadSegmentationService
{
    public function __construct(
        private readonly GroomerProfileRepository $groomerProfiles,
        private readonly EmailSuppressionRepository $emailSuppressions,
    ) {}

    public function findMatchingRecipients(Lead $lead): LeadSegmentationResult
    {
        $result = new LeadSegmentationResult();

        // Fetch candidates strictly matching city + service
        $candidates = $this->groomerProfiles->findByCityAndService($lead->getCity(), $lead->getService(), 500, 0);

        $leadPetType = self::normalize($lead->getPetType());
        $leadBreedSize = self::normalize($lead->getBreedSize());

        foreach ($candidates as $profile) {
            $email = $this->resolveEmail($profile);
            if (null === $email || '' === trim($email)) {
                $result->addExclusion($profile, 'no_email_available');
                continue;
            }

            if ($this->emailSuppressions->isSuppressed(mb_strtolower($email))) {
                $result->addExclusion($profile, 'email_suppressed');
                continue;
            }

            [$score, $matches] = $this->scoreFuzzy($profile, $leadPetType, $leadBreedSize);

            // If user provided petType or breedSize, require at least one fuzzy match.
            $requestedSpecificity = ($leadPetType !== null && $leadPetType !== '') || ($leadBreedSize !== null && $leadBreedSize !== '');
            if ($requestedSpecificity && $score <= 0.0) {
                $result->addExclusion($profile, 'no_fuzzy_match');
                continue;
            }

            // If no specificity, still include but with low baseline score.
            if (!$requestedSpecificity && $score <= 0.0) {
                $score = 0.1;
            }

            $result->addRecipient($profile, $email, $score, $matches);
        }

        $result->sortByScoreDesc();

        return $result;
    }

    private function resolveEmail(GroomerProfile $profile): ?string
    {
        $outreach = $profile->getOutreachEmail();
        if (is_string($outreach) && '' !== trim($outreach)) {
            return $outreach;
        }

        $user = $profile->getUser();
        $userEmail = $user?->getEmail();
        return is_string($userEmail) && '' !== trim($userEmail) ? $userEmail : null;
    }

    /**
     * Compute a fuzzy score based on pet type and breed size appearing
     * in servicesOffered and specialties. Returns [score, matches].
     *
     * @return array{0: float, 1: string[]}
     */
    private function scoreFuzzy(GroomerProfile $profile, ?string $petType, ?string $breedSize): array
    {
        $haystackTokens = $this->extractProfileTokens($profile);

        $matches = [];
        $score = 0.0;

        if ($petType) {
            $petKeywords = $this->expandPetTypeKeywords($petType);
            $petHits = $this->anyTokenMatches($haystackTokens, $petKeywords);
            if ($petHits) {
                $score += 1.0;
                $matches = array_merge($matches, $petHits);
            }
        }

        if ($breedSize) {
            $sizeKeywords = $this->expandBreedSizeKeywords($breedSize);
            $sizeHits = $this->anyTokenMatches($haystackTokens, $sizeKeywords);
            if ($sizeHits) {
                $score += 0.7; // size match is useful but slightly less strong than pet type
                $matches = array_merge($matches, $sizeHits);
            }
        }

        // Slight bonus for verified badge if present
        if ($profile->isVerified()) {
            $score += 0.1;
        }

        $matches = array_values(array_unique($matches));

        return [$score, $matches];
    }

    /**
     * @return array<int, string>
     */
    private function extractProfileTokens(GroomerProfile $profile): array
    {
        $servicesOffered = (string) ($profile->getServicesOffered() ?? '');
        $specialties = $profile->getSpecialties();
        $specialtiesStr = is_array($specialties) ? implode(' ', array_map(static fn($s) => (string) $s, $specialties)) : '';

        $combined = trim($servicesOffered.' '.$specialtiesStr);
        $normalized = self::normalize($combined) ?? '';

        // Tokenize by non-word boundaries after normalization
        $tokens = preg_split('/\W+/u', $normalized) ?: [];

        // Also include some common bigrams present literally in text
        $bigrams = $this->bigrams($normalized);

        return array_values(array_filter(array_unique(array_merge($tokens, $bigrams)), static fn($t) => $t !== ''));
    }

    /**
     * @param array<int, string> $haystackTokens
     * @param array<int, string> $needles
     * @return string[] matched needles
     */
    private function anyTokenMatches(array $haystackTokens, array $needles): array
    {
        $matches = [];
        $hay = $haystackTokens;
        foreach ($needles as $needle) {
            foreach ($hay as $token) {
                if ($needle === $token) {
                    $matches[] = $needle;
                    break;
                }
                // allow substring containment for simple fuzziness
                if (str_contains($token, $needle) || str_contains($needle, $token)) {
                    $matches[] = $needle;
                    break;
                }
            }
        }

        return array_values(array_unique($matches));
    }

    /**
     * @return array<int, string>
     */
    private function expandPetTypeKeywords(string $petType): array
    {
        $pt = self::normalize($petType) ?? '';
        $keywords = [];

        if ('dog' === $pt || str_contains($pt, 'dog')) {
            $keywords = ['dog', 'dogs', 'puppy', 'puppies', 'canine'];
        } elseif ('cat' === $pt || str_contains($pt, 'cat')) {
            $keywords = ['cat', 'cats', 'kitten', 'kittens', 'feline'];
        } else {
            // fallback to the provided type token itself
            $keywords = [$pt];
        }

        return $keywords;
    }

    /**
     * @return array<int, string>
     */
    private function expandBreedSizeKeywords(string $size): array
    {
        $sz = self::normalize($size) ?? '';
        // Normalize common synonyms
        $map = [
            'xs' => 'small', 'sm' => 'small', 'sml' => 'small', 'toy' => 'small', 'mini' => 'small',
            'md' => 'medium', 'med' => 'medium',
            'lg' => 'large', 'lrg' => 'large', 'big' => 'large', 'xl' => 'large', 'giant' => 'large',
        ];
        $base = $map[$sz] ?? $sz;

        $variants = [$base];
        if ('small' === $base) {
            $variants = array_merge($variants, ['toy', 'mini', 'xs', 'small-dog', 'small-breed']);
        } elseif ('medium' === $base) {
            $variants = array_merge($variants, ['mid', 'mid-size', 'medium-dog', 'medium-breed']);
        } elseif ('large' === $base) {
            $variants = array_merge($variants, ['big', 'xl', 'giant', 'large-dog', 'large-breed']);
        }

        // Add useful bigrams explicitly to match phrases in text
        $withPhrases = [];
        foreach ($variants as $v) {
            $withPhrases[] = $v;
            $withPhrases[] = $v.' dog';
            $withPhrases[] = $v.' dogs';
            $withPhrases[] = $v.' breed';
            $withPhrases[] = $v.' breeds';
        }

        // Normalize and unique
        $withPhrases = array_values(array_unique(array_filter(array_map(static fn($w) => self::normalize($w) ?? '', $withPhrases))));

        return $withPhrases;
    }

    /**
     * @return array<int, string> bigram tokens based on whitespace splitting
     */
    private function bigrams(string $text): array
    {
        $words = array_values(array_filter(preg_split('/\s+/u', $text) ?: []));
        $bigrams = [];
        $n = count($words);
        for ($i = 0; $i < $n - 1; $i++) {
            $bigrams[] = trim($words[$i].' '.$words[$i + 1]);
        }
        return $bigrams;
    }

    private static function normalize(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $v = mb_strtolower(trim($value));
        // Remove punctuation, keep letters, numbers, and spaces for phrase matching
        $v = preg_replace('/[^\p{L}\p{N}\s-]+/u', '', $v) ?? $v;
        // Collapse internal whitespace
        $v = preg_replace('/\s+/u', ' ', $v) ?? $v;

        return $v;
    }
}

