<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\FeatureFlags;
use App\Dto\Lead\LeadSubmissionDto;
use App\Service\Lead\LeadSubmissionService;
use App\Service\Lead\LeadSubmissionValidator;
use App\Service\Captcha\CaptchaVerifierInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class LeadController extends AbstractController
{
    public function __construct(
        private readonly FeatureFlags $featureFlags,
        private readonly LeadSubmissionValidator $validator,
        private readonly LeadSubmissionService $submissionService,
        private readonly CaptchaVerifierInterface $captcha,
    ) {
    }

    #[Route(path: '/lead', name: 'lead_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        if (!$this->featureFlags->isLeadsEnabled()) {
            throw $this->createNotFoundException();
        }

        $dto = $this->buildDtoFromRequest($request);

        // Early CAPTCHA verification (short-circuit on failure)
        if (!$this->captcha->verify($dto->captchaToken, $dto->clientIp)) {
            $message = ($dto->captchaToken === null || trim($dto->captchaToken) === '')
                ? 'Please complete the CAPTCHA to submit.'
                : 'Captcha could not be verified. Please try again.';

            $this->addFlash('form_error', $message);
            // Preserve submitted values so the user doesn't need to retype
            $this->addFlash('form_values', [
                'full_name' => $dto->fullName,
                'phone' => $dto->phone,
                'email' => $dto->email,
                'pet_type' => $dto->petType,
                'breed_size' => $dto->breedSize,
                'consent_to_share' => $dto->consentToShare,
            ]);

            // Redirect back to the form with anchor for better UX
            $url = $this->generateUrl('app_groomer_list_by_city_service', [
                'citySlug' => $dto->citySlug,
                'serviceSlug' => $dto->serviceSlug,
            ]);
            return $this->redirect($url.'#get-started', Response::HTTP_FOUND);
        }

        $errors = $this->validator->validate($dto);

        if (!empty($errors)) {
            return $this->render('lead/submit_error.html.twig', [
                'errors' => $errors,
            ], new Response('', Response::HTTP_BAD_REQUEST));
        }

        $lead = $this->submissionService->create($dto);
        $city = $lead->getCity();
        $service = $lead->getService();

        return $this->render('lead/thank_you.html.twig', [
            'city' => $city,
            'service' => $service,
            'lead' => $lead,
        ]);
    }

    private function buildDtoFromRequest(Request $request): LeadSubmissionDto
    {
        $dto = new LeadSubmissionDto();
        $dto->citySlug = (string) $request->request->get('city', '');
        $dto->serviceSlug = (string) $request->request->get('service', '');
        $dto->fullName = trim((string) $request->request->get('full_name', ''));
        $dto->phone = trim((string) $request->request->get('phone', ''));
        $dto->petType = trim((string) $request->request->get('pet_type', ''));
        $dto->breedSize = trim((string) $request->request->get('breed_size', ''));

        $email = (string) $request->request->get('email', '');
        $dto->email = ($email !== '') ? trim($email) : null;

        $dto->consentToShare = (bool) $request->request->get('consent_to_share', false);
        $dto->honeypot = (string) $request->request->get('website', '');
        // Capture token from either reCAPTCHA or hCaptcha, depending on provider
        $recaptcha = $request->request->get('g-recaptcha-response');
        $hcaptcha = $request->request->get('h-captcha-response');
        $dto->captchaToken = (string) ($recaptcha ?? $hcaptcha ?? '');
        $dto->clientIp = $request->getClientIp();

        return $dto;
    }
}
