<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Lead;
use App\Repository\LeadRepository;
use App\Service\Lead\LeadClaimProcessor;
use App\Service\Lead\LeadClaimRequest;
use App\Service\Lead\LeadClaimValidationResult;
use App\Service\Lead\LeadClaimValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class LeadClaimController extends AbstractController
{
    public function __construct(
        private readonly LeadClaimValidator $validator,
        private readonly LeadClaimProcessor $processor,
        private readonly LeadRepository $leads,
        private readonly \App\Service\Lead\LeadUrlBuilder $urlBuilder,
        private readonly bool $featureLeadClaimUndo = false,
    ) {}

    #[Route(path: '/leads/claim', name: 'lead_claim', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $dto = LeadClaimRequest::fromRaw(
            uri: $request->getUri(),
            lid: (string) $request->query->get('lid', ''),
            rid: (string) $request->query->get('rid', ''),
            email: (string) $request->query->get('email', ''),
            token: (string) $request->query->get('token', ''),
            exp: (string) $request->query->get('exp', ''),
        );

        $validation = $this->validator->validate($dto);

        // Handle invalid/edge outcomes including graceful re-show
        if (!$validation->isOk()) {
            if ($validation->getCode() === LeadClaimValidationResult::ALLOW_GUEST_RESHOW && $validation->getLead() instanceof Lead) {
                $lead = $validation->getLead();
                return $this->render('lead/claim_success.html.twig', [
                    'lead' => $lead,
                    'serviceName' => $lead->getService()->getName(),
                    'cityName' => $lead->getCity()->getName(),
                    'groomerName' => null,
                    'groomerPhone' => null,
                    'isGuestClaim' => true,
                ]);
            }

            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => $validation->getCode(),
            ], new Response('', $validation->httpStatus()));
        }

        // Process claim via dedicated application service
        $lead = $validation->getLead();
        $recipient = $validation->getRecipient();
        \assert($lead instanceof Lead && $recipient !== null);

        $outcome = $this->processor->process($lead, $recipient);

        if ($outcome->isSuccess()) {
            $this->addFlash('success', 'Lead claimed successfully.');
            $releaseUrl = null;
            $releaseUntil = null;
            if ($this->featureLeadClaimUndo) {
                $allowedUntil = $recipient->getReleaseAllowedUntil();
                if ($lead->getId() !== null && $recipient->getId() !== null && $allowedUntil instanceof \DateTimeImmutable) {
                    $releaseUrl = $this->urlBuilder->buildSignedReleaseUrl((int) $lead->getId(), (int) $recipient->getId(), $allowedUntil);
                    $releaseUntil = $allowedUntil;
                }
            }
            return $this->render('lead/claim_success.html.twig', [
                'lead' => $lead,
                'serviceName' => $lead->getService()->getName(),
                'cityName' => $lead->getCity()->getName(),
                'groomerName' => null,
                'groomerPhone' => null,
                'isGuestClaim' => true,
                'undoEnabled' => $this->featureLeadClaimUndo,
                'releaseUrl' => $releaseUrl,
                'releaseUntil' => $releaseUntil,
            ]);
        }

        $status = $outcome->getCode() === 'already_claimed' ? Response::HTTP_CONFLICT : Response::HTTP_BAD_REQUEST;
        return $this->render('lead/claim_invalid.html.twig', [
            'reason' => $outcome->getCode(),
        ], new Response('', $status));
    }

    // Removed insecure success endpoint that exposed lead details without validation.
}
