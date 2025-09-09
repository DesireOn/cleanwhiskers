<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\Lead;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Twig\Environment;

final class OutreachEmailFactory
{
    public function __construct(
        private readonly string $fromAddress,
        private readonly string $companyName,
        private readonly string $companyAddress = '',
        private readonly string $contactEmail = '',
        private readonly ?Environment $twig = null,
    ) {}

    public function buildLeadInviteEmail(Lead $lead, string $to, string $claimUrl, string $unsubUrl, \DateTimeImmutable $expiresAt): Email
    {
        // Subject expected by tests: "New <Service> lead in <City>"
        $subject = sprintf('New %s lead in %s', $lead->getService()->getName(), $lead->getCity()->getName());
        $text = $this->renderPlainTextEmail($lead, $claimUrl, $unsubUrl, $expiresAt);

        if ($this->twig instanceof Environment) {
            $email = (new TemplatedEmail())
                ->from($this->fromAddress)
                ->to($to)
                ->subject($subject)
                ->htmlTemplate('emails/lead_invite.html.twig')
                ->context([
                    'lead' => $lead,
                    'serviceName' => $lead->getService()->getName(),
                    'cityName' => $lead->getCity()->getName(),
                    'claimUrl' => $claimUrl,
                    'unsubUrl' => $unsubUrl,
                    'expiresAt' => $expiresAt,
                    'expiresIn' => $this->humanizeTimeUntil($expiresAt),
                    'companyName' => $this->companyName,
                    'companyAddress' => $this->companyAddress,
                    'contactEmail' => $this->contactEmail ?: $this->fromAddress,
                ])
                ->text($text);

            return $email;
        }

        // Fallback if Twig not available
        return (new Email())
            ->from($this->fromAddress)
            ->to($to)
            ->subject($subject)
            ->text($text);
    }

    private function renderPlainTextEmail(Lead $lead, string $claimUrl, string $unsubUrl, \DateTimeImmutable $expiresAt): string
    {
        // Keep this concise to satisfy unit tests expectations
        $lines = [];
        $lines[] = sprintf('New %s lead in %s', $lead->getService()->getName(), $lead->getCity()->getName());
        $lines[] = sprintf('Claim this lead: %s', $claimUrl);
        // Expiry shown as a relative time to avoid timezone confusion
        $lines[] = sprintf('This link expires %s', $this->humanizeTimeUntil($expiresAt));
        $lines[] = sprintf('unsubscribe here: %s', $unsubUrl);
        $lines[] = sprintf('— %s', $this->companyName);
        return implode("\n", $lines);
    }

    /**
     * Returns a compact human-friendly relative time for a future date.
     * Examples: "in 5 minutes", "in an hour", "in 3 hours", "in 2 days".
     */
    private function humanizeTimeUntil(\DateTimeImmutable $future, ?\DateTimeImmutable $now = null): string
    {
        $now ??= new \DateTimeImmutable('now');
        $diff = $future->getTimestamp() - $now->getTimestamp();

        if ($diff <= 0) {
            return 'soon';
        }

        $minute = 60;
        $hour   = 60 * $minute;
        $day    = 24 * $hour;

        if ($diff < 90) {
            return 'in less than 2 minutes';
        }

        if ($diff < $hour) {
            $mins = (int) round($diff / $minute);
            if ($mins <= 1) {
                return 'in a minute';
            }
            return sprintf('in %d minutes', $mins);
        }

        if ($diff < 2 * $hour) {
            return 'in an hour';
        }

        if ($diff < $day) {
            $hours = (int) round($diff / $hour);
            return sprintf('in %d hours', max(2, $hours));
        }

        $days = (int) round($diff / $day);
        if ($days <= 1) {
            return 'in a day';
        }
        return sprintf('in %d days', $days);
    }

    public function buildLeadAlreadyClaimedEmail(Lead $lead, string $to, string $unsubUrl): Email
    {
        $subject = sprintf('Lead already claimed: %s in %s', $lead->getService()->getName(), $lead->getCity()->getName());

        // Provide a brief text alternative
        $textLines = [];
        $textLines[] = sprintf('The %s lead in %s has already been claimed.', $lead->getService()->getName(), $lead->getCity()->getName());
        $textLines[] = 'We will notify you about future opportunities.';
        $textLines[] = sprintf('Unsubscribe: %s', $unsubUrl);
        $textLines[] = sprintf('— %s', $this->companyName);
        $text = implode("\n", $textLines);

        if ($this->twig instanceof Environment) {
            return (new TemplatedEmail())
                ->from($this->fromAddress)
                ->to($to)
                ->subject($subject)
                ->htmlTemplate('emails/lead_already_claimed.html.twig')
                ->context([
                    'lead' => $lead,
                    'serviceName' => $lead->getService()->getName(),
                    'cityName' => $lead->getCity()->getName(),
                    'unsubUrl' => $unsubUrl,
                    'companyName' => $this->companyName,
                    'companyAddress' => $this->companyAddress,
                    'contactEmail' => $this->contactEmail ?: $this->fromAddress,
                ])
                ->text($text);
        }

        return (new Email())
            ->from($this->fromAddress)
            ->to($to)
            ->subject($subject)
            ->text($text);
    }
}
