<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\Lead;
use Symfony\Component\Mime\Email;

final class OutreachEmailFactory
{
    public function __construct(
        private readonly string $fromAddress,
        private readonly string $companyName,
    ) {}

    public function buildLeadInviteEmail(Lead $lead, string $to, string $claimUrl, string $unsubUrl, \DateTimeImmutable $expiresAt): Email
    {
        $subject = sprintf('New %s lead in %s', $lead->getService()->getName(), $lead->getCity()->getName());
        $text = $this->renderPlainTextEmail($lead, $claimUrl, $unsubUrl, $expiresAt);

        return (new Email())
            ->from($this->fromAddress)
            ->to($to)
            ->subject($subject)
            ->text($text);
    }

    private function renderPlainTextEmail(Lead $lead, string $claimUrl, string $unsubUrl, \DateTimeImmutable $expiresAt): string
    {
        $lines = [];
        $lines[] = sprintf('You have a new %s lead in %s.', $lead->getService()->getName(), $lead->getCity()->getName());
        $lines[] = '';
        $lines[] = sprintf('Claim this lead: %s', $claimUrl);
        $lines[] = sprintf('This claim link expires on %s.', $expiresAt->format('Y-m-d H:i T'));
        $lines[] = '';
        $lines[] = sprintf('If you no longer wish to receive outreach emails, unsubscribe here: %s', $unsubUrl);
        $lines[] = '';
        $lines[] = sprintf('— %s', $this->companyName);
        return implode("\n", $lines);
    }
}

