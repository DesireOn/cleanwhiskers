<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Lead\LeadClaimRequest;
use App\Service\Lead\LeadClaimValidator;
use App\Service\Lead\LeadWaitlistManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class LeadWaitlistController extends AbstractController
{
    public function __construct(
        private readonly LeadClaimValidator $validator,
        private readonly LeadWaitlistManager $manager,
    ) {
    }

    #[Route(path: '/leads/waitlist/join', name: 'lead_waitlist_join', methods: ['POST'])]
    public function join(Request $request): Response
    {
        // Reuse the same signed params as claim links; the frontend will POST to this route
        // carrying the original query string (lid, rid, email, token, exp, signature).
        // The signature was generated for the claim URL, not this join endpoint.
        // Reconstruct the original claim URL with the same query string so UriSigner::check() matches.
        $originalClaimUrl = $request->getSchemeAndHttpHost() . '/leads/claim';
        $queryString = $request->getQueryString();
        if ($queryString !== null && $queryString !== '') {
            $originalClaimUrl .= '?' . $queryString;
        }

        $dto = LeadClaimRequest::fromRaw(
            uri: $originalClaimUrl,
            lid: (string) $request->query->get('lid', ''),
            rid: (string) $request->query->get('rid', ''),
            email: (string) $request->query->get('email', ''),
            token: (string) $request->query->get('token', ''),
            exp: (string) $request->query->get('exp', ''),
        );

        $validation = $this->validator->validate($dto);
        if (!$validation->isOk()) {
            return new JsonResponse(['error' => $validation->getCode()], $validation->httpStatus());
        }

        $lead = $validation->getLead();
        $recipient = $validation->getRecipient();
        \assert($lead !== null && $recipient !== null);

        $result = $this->manager->join($lead, $recipient);
        $status = $result['created'] ? Response::HTTP_CREATED : Response::HTTP_OK;

        return new JsonResponse([
            'status' => $result['created'] ? 'joined' : 'exists',
        ], $status);
    }
}
