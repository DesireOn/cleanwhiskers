<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Lead;
use App\Repository\LeadRecipientRepository;
use App\Repository\LeadRepository;
use App\Repository\AuditLogRepository;
use App\Service\Lead\LeadClaimService;
use App\Service\Lead\LeadUrlBuilder;
use App\Service\Lead\OutreachEmailFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Annotation\Route;

final class LeadClaimController extends AbstractController
{
    public function __construct(
        private readonly UriSigner $signer,
        private readonly LeadRepository $leads,
        private readonly LeadRecipientRepository $recipients,
        private readonly LeadClaimService $claimService,
        private readonly AuditLogRepository $auditLogs,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
        private readonly \Symfony\Component\Mailer\MailerInterface $mailer,
        private readonly LeadUrlBuilder $urlBuilder,
        private readonly OutreachEmailFactory $emailFactory,
    ) {
    }

    #[Route(path: '/leads/claim', name: 'lead_claim', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $uri = $request->getUri();

        // Basic signed-link and expiry validation
        $isSigned = $this->signer->check($uri);
        $exp = (string) $request->query->get('exp', '');
        $now = time();
        $notExpired = ctype_digit($exp) && (int) $exp >= $now;

        if (!$isSigned || !$notExpired) {
            // Attempt graceful fallback: if this exact recipient already claimed the lead,
            // allow viewing details again even if link expired/invalid.
            $lid = (string) $request->query->get('lid', '');
            $rid = (string) $request->query->get('rid', '');
            $email = (string) $request->query->get('email', '');
            $token = (string) $request->query->get('token', '');

            if (ctype_digit($lid) && ctype_digit($rid) && $email !== '' && $token !== '') {
                $lead = $this->leads->find((int) $lid);
                $recipient = $this->recipients->find((int) $rid);

                if ($lead instanceof Lead && $recipient !== null && $recipient->getLead()->getId() === $lead->getId()) {
                    $normalizedEmail = mb_strtolower(trim($email));
                    $hashOk = hash('sha256', $token) === $recipient->getClaimTokenHash();
                    $emailOk = $normalizedEmail !== '' && $normalizedEmail === mb_strtolower($recipient->getEmail());

                    if ($hashOk && $emailOk && $recipient->getClaimedAt() !== null) {
                        $this->logger->info('Lead claim link expired/invalid but recipient previously claimed; rendering details (guest)');

                        return $this->render('lead/claim_success.html.twig', [
                            'lead' => $lead,
                            'serviceName' => $lead->getService()->getName(),
                            'cityName' => $lead->getCity()->getName(),
                            'groomerName' => null,
                            'groomerPhone' => null,
                            'isGuestClaim' => true,
                        ]);
                    }
                }
            }

            $reason = !$isSigned ? 'invalid_signature' : 'expired';
            $this->logger->info('Lead claim denied: invalid or expired link', [
                'signed' => $isSigned,
                'exp' => $exp,
                'reason' => $reason,
            ]);

            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => $reason,
            ], new Response('', Response::HTTP_BAD_REQUEST));
        }

        // Extract and validate required params
        $lid = (string) $request->query->get('lid', '');
        $rid = (string) $request->query->get('rid', '');
        $email = (string) $request->query->get('email', '');
        $token = (string) $request->query->get('token', '');

        if (!ctype_digit($lid) || !ctype_digit($rid) || $email === '' || $token === '') {
            $this->logger->info('Lead claim denied: missing parameters', [
                'lid' => $lid,
                'rid' => $rid,
                'email' => $email !== '',
                'token' => $token !== '',
            ]);
            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => 'missing_params',
            ], new Response('', Response::HTTP_BAD_REQUEST));
        }

        $lead = $this->leads->find((int) $lid);
        $recipient = $this->recipients->find((int) $rid);

        if ($lead === null || $recipient === null || $recipient->getLead()->getId() !== $lead->getId()) {
            $this->logger->info('Lead claim denied: lead/recipient not found or mismatch', [
                'lid' => $lid,
                'rid' => $rid,
            ]);
            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => 'not_found',
            ], new Response('', Response::HTTP_NOT_FOUND));
        }

        // Sanity checks: email and token hash match, and token not expired
        $normalizedEmail = mb_strtolower(trim($email));
        $hashOk = hash('sha256', $token) === $recipient->getClaimTokenHash();
        $emailOk = $normalizedEmail !== '' && $normalizedEmail === mb_strtolower($recipient->getEmail());
        $recipientNotExpired = $recipient->getTokenExpiresAt()->getTimestamp() >= $now;

        if (!$hashOk || !$emailOk || !$recipientNotExpired) {
            $reason = !$recipientNotExpired ? 'expired' : 'invalid_token_or_email';
            $this->logger->info('Lead claim denied: token/email mismatch or expired', [
                'hashOk' => $hashOk,
                'emailOk' => $emailOk,
                'recipientExp' => $recipient->getTokenExpiresAt()->getTimestamp(),
                'now' => $now,
                'reason' => $reason,
            ]);
            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => $reason,
            ], new Response('', Response::HTTP_BAD_REQUEST));
        }

        // Treat all claims as guest claims (no authenticated groomer logic)
        $isGuestClaim = true;

        // Audit: record a claim attempt (guest)
        if ($lead->getId() !== null && $recipient->getId() !== null) {
            $this->auditLogs->log(
                event: 'claim_attempt',
                subjectType: \App\Entity\AuditLog::SUBJECT_LEAD,
                subjectId: (int) $lead->getId(),
                metadata: [
                    'recipientId' => $recipient->getId(),
                    'recipientEmail' => $recipient->getEmail(),
                    'guest' => true,
                    'guestEmail' => $normalizedEmail,
                ],
                actorType: \App\Entity\AuditLog::ACTOR_GROOMER,
                actorId: null,
            );
            $this->em->flush();
        }

        // Attempt to claim atomically via service (with DB lock)
        $result = $this->claimService->claim($lead, $recipient, null);

        if (!$result->isSuccess()) {
            $code = $result->getCode();
            // If this specific recipient already claimed the lead earlier, show success again
            if ($code === 'already_claimed' && $recipient->getClaimedAt() !== null) {
                return $this->render('lead/claim_success.html.twig', [
                    'lead' => $lead,
                    'serviceName' => $lead->getService()->getName(),
                    'cityName' => $lead->getCity()->getName(),
                    'groomerName' => null,
                    'groomerPhone' => null,
                    'isGuestClaim' => true,
                ]);
            }

            $status = $code === 'already_claimed' ? Response::HTTP_CONFLICT : Response::HTTP_BAD_REQUEST;

            // Audit: conflict when already claimed
            if ($code === 'already_claimed' && $lead->getId() !== null && $recipient->getId() !== null) {
                $this->auditLogs->log(
                    event: 'claim_conflict',
                    subjectType: \App\Entity\AuditLog::SUBJECT_LEAD,
                    subjectId: (int) $lead->getId(),
                    metadata: [
                        'recipientId' => $recipient->getId(),
                        'recipientEmail' => $recipient->getEmail(),
                        'groomerId' => null,
                        'leadStatus' => $lead->getStatus(),
                        'claimedById' => $lead->getClaimedBy()?->getId(),
                    ],
                    actorType: \App\Entity\AuditLog::ACTOR_GROOMER,
                    actorId: null,
                );
                $this->em->flush();
            }

            $this->logger->info('Lead claim failed', [
                'groomerId' => null,
                'leadId' => $lead->getId(),
                'recipientId' => $recipient->getId(),
                'code' => $code,
            ]);

            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => $code,
            ], new Response('', $status));
        }

        // Audit: claim success (guest)
        if ($lead->getId() !== null && $recipient->getId() !== null) {
            $this->auditLogs->log(
                event: 'claim_success',
                subjectType: \App\Entity\AuditLog::SUBJECT_LEAD,
                subjectId: (int) $lead->getId(),
                metadata: [
                    'recipientId' => $recipient->getId(),
                    'recipientEmail' => $recipient->getEmail(),
                    'groomerId' => null,
                    'guest' => true,
                ],
                actorType: \App\Entity\AuditLog::ACTOR_GROOMER,
                actorId: null,
            );
            $this->em->flush();
        }

        // Email the claimer with lead details and next steps
        try {
            $unsubUrl = $this->urlBuilder->buildSignedUnsubscribeUrl($recipient->getEmail());
            $email = $this->emailFactory->buildLeadClaimSuccessEmail($lead, $recipient->getEmail(), null, $unsubUrl);
            $this->mailer->send($email);
        } catch (\Throwable $e) {
            $this->logger->error('Failed sending claim success email', [
                'leadId' => $lead->getId(),
                'recipientId' => $recipient->getId(),
                'email' => $recipient->getEmail(),
                'error' => $e->getMessage(),
            ]);
        }

        // Render success directly so guests don't need registration
        $this->addFlash('success', 'Lead claimed successfully.');
        return $this->render('lead/claim_success.html.twig', [
            'lead' => $lead,
            'serviceName' => $lead->getService()->getName(),
            'cityName' => $lead->getCity()->getName(),
            'groomerName' => null,
            'groomerPhone' => null,
            'isGuestClaim' => true,
        ]);
    }

    #[Route(path: '/leads/claim/success', name: 'lead_claim_success', methods: ['GET'])]
    public function success(Request $request): Response
    {
        $lid = (string) $request->query->get('lid', '');
        if (!ctype_digit($lid)) {
            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => 'missing_params',
            ], new Response('', Response::HTTP_BAD_REQUEST));
        }

        $lead = $this->leads->find((int) $lid);
        if (!$lead instanceof Lead) {
            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => 'not_found',
            ], new Response('', Response::HTTP_NOT_FOUND));
        }

        // Guests can view the success page with basic lead info
        return $this->render('lead/claim_success.html.twig', [
            'lead' => $lead,
            'serviceName' => $lead->getService()->getName(),
            'cityName' => $lead->getCity()->getName(),
            'groomerName' => null,
            'groomerPhone' => null,
            'isGuestClaim' => true,
        ]);
    }
}
