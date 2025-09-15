<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Lead;
use App\Repository\LeadRecipientRepository;
use App\Repository\LeadRepository;
use App\Service\Lead\LeadReleaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\UriSigner;

final class LeadReleaseController extends AbstractController
{
    public function __construct(
        private readonly UriSigner $signer,
        private readonly LeadRepository $leads,
        private readonly LeadRecipientRepository $recipients,
        private readonly LeadReleaseService $releaseService,
    ) {}

    #[Route(path: '/leads/release', name: 'lead_release', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        // Basic param extraction
        $lid = (string) $request->query->get('lid', '');
        $rid = (string) $request->query->get('rid', '');
        $exp = (string) $request->query->get('exp', '');

        // Validate presence
        if (!ctype_digit($lid) || !ctype_digit($rid) || !ctype_digit($exp)) {
            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => 'missing_params',
            ], new Response('', Response::HTTP_BAD_REQUEST));
        }

        // Signature + expiry check
        $isSigned = $this->signer->check($request->getUri());
        $nowTs = time();
        $expValid = (int) $exp >= $nowTs;
        if (!$isSigned || !$expValid) {
            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => $isSigned ? 'expired' : 'invalid_signature',
            ], new Response('', Response::HTTP_BAD_REQUEST));
        }

        $lead = $this->leads->find((int) $lid);
        $recipient = $this->recipients->find((int) $rid);
        if (!$lead instanceof Lead || $recipient === null || $recipient->getLead()->getId() !== $lead->getId()) {
            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => 'not_found',
            ], new Response('', Response::HTTP_NOT_FOUND));
        }

        $outcome = $this->releaseService->release($lead, $recipient);
        if ($outcome->isSuccess()) {
            return $this->render('lead/release_success.html.twig', [
                'lead' => $lead,
                'serviceName' => $lead->getService()->getName(),
                'cityName' => $lead->getCity()->getName(),
            ]);
        }

        $reason = $outcome->getCode();
        $status = match ($reason) {
            'not_claimed' => Response::HTTP_CONFLICT,
            'window_expired', 'invalid_recipient' => Response::HTTP_BAD_REQUEST,
            'already_released' => Response::HTTP_CONFLICT,
            default => Response::HTTP_BAD_REQUEST,
        };
        return $this->render('lead/claim_invalid.html.twig', [
            'reason' => $reason,
        ], new Response('', $status));
    }
}

