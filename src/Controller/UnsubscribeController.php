<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\EmailSuppression;
use App\Entity\LeadRecipient;
use App\Repository\AuditLogRepository;
use App\Repository\EmailSuppressionRepository;
use App\Repository\LeadRecipientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Annotation\Route;

final class UnsubscribeController extends AbstractController
{
    public function __construct(
        private readonly UriSigner $signer,
        private readonly EmailSuppressionRepository $suppressions,
        private readonly LeadRecipientRepository $recipients,
        private readonly AuditLogRepository $auditLogs,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/unsubscribe', name: 'unsubscribe', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $uri = $request->getUri();
        $email = (string) $request->query->get('email', '');
        $expires = (string) $request->query->get('expires', '');

        $normalizedEmail = $this->normalizeEmail($email);

        $isSigned = $this->signer->check($uri);
        $now = time();
        $expiryOk = ctype_digit($expires) && (int) $expires >= $now;

        if (!$isSigned || !$expiryOk || $normalizedEmail === '') {
            $this->logger->info('Unsubscribe denied: invalid link', [
                'email' => $normalizedEmail,
                'expires' => $expires,
                'signed' => $isSigned,
            ]);

            return $this->render('unsubscribe/confirm.html.twig', [
                'success' => false,
                'reason' => !$isSigned ? 'invalid_signature' : ($normalizedEmail === '' ? 'missing_email' : 'expired'),
                'email' => $normalizedEmail,
                'updatedCount' => 0,
            ]);
        }

        // Upsert into EmailSuppression
        $suppressed = $this->suppressions->findOneBy(['email' => $normalizedEmail]);
        if ($suppressed === null) {
            $suppressed = new EmailSuppression($normalizedEmail, 'user_unsubscribe');
            $this->em->persist($suppressed);
        }

        // Mark matching LeadRecipient rows as unsubscribed
        $updated = 0;
        $matches = $this->recipients->findBy(['email' => $normalizedEmail]);
        foreach ($matches as $recipient) {
            if ($recipient->getStatus() !== LeadRecipient::STATUS_UNSUBSCRIBED) {
                $recipient->setStatus(LeadRecipient::STATUS_UNSUBSCRIBED);
                $this->auditLogs->logRecipientUnsubscribed($recipient);
                $updated++;
            }
        }

        if ($updated === 0) {
            // Still record an audit trail even if no recipient rows existed
            $this->auditLogs->logUnsubscribedEmail($normalizedEmail);
        }

        $this->em->flush();

        return $this->render('unsubscribe/confirm.html.twig', [
            'success' => true,
            'email' => $normalizedEmail,
            'updatedCount' => $updated,
        ]);
    }

    private function normalizeEmail(string $email): string
    {
        $email = trim($email);
        return $email === '' ? '' : mb_strtolower($email);
    }
}

