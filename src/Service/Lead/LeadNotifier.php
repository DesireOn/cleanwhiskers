<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Service\Sms\SmsGatewayInterface;
use Doctrine\ORM\EntityManagerInterface;

final class LeadNotifier
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SmsGatewayInterface $sms,
    ) {
    }

    /**
     * Creates LeadRecipient records and sends notifications.
     *
     * @param array<int, GroomerProfile> $groomers
     * @return array<int, LeadRecipient>
     */
    public function notify(Lead $lead, array $groomers, ?callable $messageBuilder = null): array
    {
        $recipients = [];
        $now = new \DateTimeImmutable();
        foreach ($groomers as $groomer) {
            $phone = $groomer->getPhone();
            if (null === $phone) {
                continue; // skip invalid recipients
            }

            $token = $this->generateToken();
            $recipient = new LeadRecipient($lead, $groomer, $phone, $token);
            $recipient->setNotificationStatus(LeadRecipient::STATUS_QUEUED);
            $this->em->persist($recipient);
            $recipients[] = $recipient;
        }

        $this->em->flush();

        foreach ($recipients as $recipient) {
            $msg = $messageBuilder ? $messageBuilder($lead, $recipient) : $this->defaultMessage($lead, $recipient);
            try {
                $this->sms->send($recipient->getPhone(), $msg);
                $recipient->setNotificationStatus(LeadRecipient::STATUS_SENT);
                $recipient->setNotifiedAt($now);
            } catch (\Throwable) {
                $recipient->setNotificationStatus(LeadRecipient::STATUS_FAILED);
            }
        }

        $this->em->flush();

        return $recipients;
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function defaultMessage(Lead $lead, LeadRecipient $recipient): string
    {
        $cityName = $lead->getCity()->getName();
        $petType = $lead->getPetType();
        $claimUrl = sprintf('https://cleanwhiskers.com/lead/%s/claim/%s', $lead->getClaimToken(), $recipient->getRecipientToken());
        return sprintf('New %s grooming lead in %s. Claim: %s', $petType, $cityName, $claimUrl);
    }
}

