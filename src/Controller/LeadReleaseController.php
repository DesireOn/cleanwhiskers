<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Lead\LeadReleaseService;
use App\Service\Lead\LeadReleaseRequest;
use App\Service\Lead\LeadReleaseValidator;
use App\Service\Lead\LeadReleaseValidationResult;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class LeadReleaseController extends AbstractController
{
    public function __construct(
        private readonly LeadReleaseValidator $validator,
        private readonly LeadReleaseService $releaseService,
    ) {}

    #[Route(path: '/leads/release', name: 'lead_release', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $dto = LeadReleaseRequest::fromRaw(
            uri: $request->getUri(),
            lid: (string) $request->query->get('lid', ''),
            rid: (string) $request->query->get('rid', ''),
            exp: (string) $request->query->get('exp', ''),
        );

        $validation = $this->validator->validate($dto);
        if (!$validation->isOk()) {
            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => $validation->getCode(),
            ], new Response('', $validation->httpStatus()));
        }

        $lead = $validation->getLead();
        $recipient = $validation->getRecipient();
        \assert($lead !== null && $recipient !== null);

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
