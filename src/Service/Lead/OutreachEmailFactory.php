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
