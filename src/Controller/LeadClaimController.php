<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Repository\GroomerProfileRepository;
use App\Repository\LeadRecipientRepository;
use App\Repository\LeadRepository;
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

        // If not logged in as/linked to a groomer, store claim intent and redirect to register/login
        $user = $this->getUser();
        $hasGroomerProfile = false;
        if ($user !== null) {
            $profile = $this->groomers->findOneBy(['user' => $user]);
            $hasGroomerProfile = $profile instanceof GroomerProfile;
        }

        if ($user === null || !$hasGroomerProfile) {
            // Persist minimal intent in session
            $session = $request->getSession();
            $session->set('lead_claim_intent', [
                'lead_id' => (int) $lid,
                'recipient_id' => (int) $rid,
                'email' => $normalizedEmail,
            ]);

            // Prefer registering as a groomer to complete the claim
            return $this->redirectToRoute('app_register', ['role' => 'groomer']);
        }

        // Placeholder: user already linked to a groomer. Subsequent implementation can finalize the claim.
        // For now, redirect to homepage with a friendly notice.
        $this->addFlash('success', 'You are ready to claim this lead.');
        return $this->redirectToRoute('app_homepage');
    }
}
