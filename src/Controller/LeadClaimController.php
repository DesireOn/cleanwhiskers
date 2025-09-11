<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Repository\GroomerProfileRepository;
use App\Repository\LeadRecipientRepository;
use App\Repository\LeadRepository;
use App\Repository\AuditLogRepository;
use App\Service\Lead\LeadClaimPolicy;
use App\Service\Lead\LeadClaimService;
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
        private readonly GroomerProfileRepository $groomers,
        private readonly LeadClaimPolicy $claimPolicy,
        private readonly LeadClaimService $claimService,
        private readonly AuditLogRepository $auditLogs,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
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

        // Determine if user has a groomer profile; allow guest claims otherwise
        $user = $this->getUser();
        $hasGroomerProfile = false;
        if ($user !== null) {
            $profile = $this->groomers->findOneBy(['user' => $user]);
            $hasGroomerProfile = $profile instanceof GroomerProfile;
        }
        $isGuestClaim = ($user === null || !$hasGroomerProfile);

        // User already linked to a groomer — enforce claim cooldown policy
        $profile = $this->groomers->findOneBy(['user' => $user]);
        if ($profile instanceof GroomerProfile) {
            $allowance = $this->claimPolicy->canClaim($profile);
            if (!$allowance->isAllowed()) {
                // Record audit: claim attempt blocked by cooldown
                if ($lead->getId() !== null && $recipient->getId() !== null) {
                    $this->auditLogs->log(
                        event: 'claim_attempt',
                        subjectType: \App\Entity\AuditLog::SUBJECT_LEAD,
                        subjectId: (int) $lead->getId(),
                        metadata: [
                            'recipientId' => $recipient->getId(),
                            'recipientEmail' => $recipient->getEmail(),
                            'blocked' => 'cooldown',
                            'groomerId' => $profile->getId(),
                            'nextAllowedAt' => $allowance->getNextAllowedAt()?->format(DATE_ATOM),
                        ],
                        actorType: \App\Entity\AuditLog::ACTOR_GROOMER,
                        actorId: $profile->getId(),
                    );
                    $this->em->flush();
                }
                $this->logger->info('Lead claim blocked by cooldown policy', [
                    'groomerId' => $profile->getId(),
                    'reason' => $allowance->getReason(),
                    'nextAllowedAt' => $allowance->getNextAllowedAt()?->format(DATE_ATOM),
                ]);

                return $this->render('lead/claim_invalid.html.twig', [
                    'reason' => 'cooldown',
                    'cooldown_until' => $allowance->getNextAllowedAt(),
                    'cooldown_remaining_minutes' => $allowance->getRemainingMinutes(),
                ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            }
        }

        // Audit: record a claim attempt (authenticated)
        if ($lead->getId() !== null && $recipient->getId() !== null) {
            if ($isGuestClaim) {
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
            } elseif ($profile instanceof GroomerProfile) {
                $this->auditLogs->log(
                    event: 'claim_attempt',
                    subjectType: \App\Entity\AuditLog::SUBJECT_LEAD,
                    subjectId: (int) $lead->getId(),
                    metadata: [
                        'recipientId' => $recipient->getId(),
                        'recipientEmail' => $recipient->getEmail(),
                        'groomerId' => $profile->getId(),
                    ],
                    actorType: \App\Entity\AuditLog::ACTOR_GROOMER,
                    actorId: $profile->getId(),
                );
            }
            $this->em->flush();
        }

        // Attempt to claim atomically via service (with DB lock)
        $result = $this->claimService->claim($lead, $recipient, $profile instanceof GroomerProfile ? $profile : null);

        if (!$result->isSuccess()) {
            $code = $result->getCode();
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
                        'groomerId' => $profile instanceof GroomerProfile ? $profile->getId() : null,
                        'leadStatus' => $lead->getStatus(),
                        'claimedById' => $lead->getClaimedBy()?->getId(),
                    ],
                    actorType: \App\Entity\AuditLog::ACTOR_GROOMER,
                    actorId: $profile instanceof GroomerProfile ? $profile->getId() : null,
                );
                $this->em->flush();
            }

            $this->logger->info('Lead claim failed', [
                'groomerId' => $profile instanceof GroomerProfile ? $profile->getId() : null,
                'leadId' => $lead->getId(),
                'recipientId' => $recipient->getId(),
                'code' => $code,
            ]);

            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => $code,
            ], new Response('', $status));
        }

        // Audit: claim success
        if ($lead->getId() !== null && $recipient->getId() !== null) {
            $this->auditLogs->log(
                event: 'claim_success',
                subjectType: \App\Entity\AuditLog::SUBJECT_LEAD,
                subjectId: (int) $lead->getId(),
                metadata: [
                    'recipientId' => $recipient->getId(),
                    'recipientEmail' => $recipient->getEmail(),
                    'groomerId' => $profile instanceof GroomerProfile ? $profile->getId() : null,
                    'guest' => $isGuestClaim,
                ],
                actorType: \App\Entity\AuditLog::ACTOR_GROOMER,
                actorId: $profile instanceof GroomerProfile ? $profile->getId() : null,
            );
            $this->em->flush();
        }

        // Render success directly so guests don't need registration
        $this->addFlash('success', 'Lead claimed successfully.');
        return $this->render('lead/claim_success.html.twig', [
            'lead' => $lead,
            'serviceName' => $lead->getService()->getName(),
            'cityName' => $lead->getCity()->getName(),
            'groomerName' => $profile instanceof GroomerProfile ? $profile->getBusinessName() : null,
            'groomerPhone' => $profile instanceof GroomerProfile ? $profile->getPhone() : null,
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

        $user = $this->getUser();
        $profile = null;
        if ($user !== null) {
            $profile = $this->groomers->findOneBy(['user' => $user]);
        }

        // Only the groomer who claimed this lead can view the success page
        if (!$profile instanceof GroomerProfile || $lead->getClaimedBy()?->getId() !== $profile->getId()) {
            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => 'not_found',
            ], new Response('', Response::HTTP_NOT_FOUND));
        }

        return $this->render('lead/claim_success.html.twig', [
            'lead' => $lead,
            'serviceName' => $lead->getService()->getName(),
            'cityName' => $lead->getCity()->getName(),
            'groomerName' => $profile->getBusinessName(),
            'groomerPhone' => $profile->getPhone(),
        ]);
    }
}
