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
            return $this->render('lead/claim_success.html.twig', [
                'lead' => $lead,
                'serviceName' => $lead->getService()->getName(),
                'cityName' => $lead->getCity()->getName(),
                'groomerName' => null,
                'groomerPhone' => null,
                'isGuestClaim' => true,
            ]);
        }

        $status = $outcome->getCode() === 'already_claimed' ? Response::HTTP_CONFLICT : Response::HTTP_BAD_REQUEST;
        return $this->render('lead/claim_invalid.html.twig', [
            'reason' => $outcome->getCode(),
        ], new Response('', $status));
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
