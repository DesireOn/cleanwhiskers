<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Lead;
use App\Message\DispatchLeadMessage;
use App\Repository\CityRepository;
use App\Repository\ServiceRepository;
use App\Service\Captcha\CaptchaVerifierInterface;
use App\Service\FeatureFlags;
use App\Service\Lead\LeadTokenFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

final class LeadController extends AbstractController
{
    public function __construct(
        private readonly FeatureFlags $featureFlags,
        private readonly CityRepository $cities,
        private readonly ServiceRepository $services,
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $bus,
        private readonly LeadTokenFactory $leadTokenFactory,
        private readonly CaptchaVerifierInterface $captcha,
    ) {
    }

    #[Route(path: '/lead', name: 'lead_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        if (!$this->featureFlags->isLeadsEnabled()) {
            throw $this->createNotFoundException();
        }

        $citySlug = (string) $request->request->get('city', '');
        $serviceSlug = (string) $request->request->get('service', '');
        $fullName = trim((string) $request->request->get('full_name', ''));
        $phone = trim((string) $request->request->get('phone', ''));
        $petType = $this->n((string) $request->request->get('pet_type', ''));
        $breedSize = $this->n((string) $request->request->get('breed_size', ''));
        $email = $this->n((string) $request->request->get('email', ''));
        $consent = (bool) $request->request->get('consent_to_share', false);
        $honeypot = $this->n((string) $request->request->get('website', '')); // simple honeypot field
        $captchaToken = $this->n((string) $request->request->get('g-recaptcha-response', ''));

        $errors = [];
        if ($honeypot !== null && $honeypot !== '') {
            $errors[] = 'Invalid submission.';
        }
        if ($citySlug === '' || null === ($city = $this->cities->findOneBySlug($citySlug))) {
            $errors[] = 'Please select a valid city.';
        }
        if ($serviceSlug === '' || null === ($service = $this->services->findOneBySlug($serviceSlug))) {
            $errors[] = 'Please select a valid service.';
        }
        if ($fullName === '') {
            $errors[] = 'Full name is required.';
        }
        if ($phone === '' || !$this->isValidPhone($phone)) {
            $errors[] = 'Please provide a valid phone number.';
        }
        if ($petType === null || $petType === '') {
            $errors[] = 'Please select your pet type.';
        }
        if ($email !== null && $email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid email address.';
        }
        if (!$this->captcha->verify($captchaToken, $request->getClientIp())) {
            $errors[] = 'CAPTCHA verification failed.';
        }

        if (!empty($errors)) {
            return $this->render('lead/submit_error.html.twig', [
                'errors' => $errors,
            ], new Response('', Response::HTTP_BAD_REQUEST));
        }

        $city = $city ?? $this->cities->findOneBySlug($citySlug);
        $service = $service ?? $this->services->findOneBySlug($serviceSlug);

        // Email is currently optional in UI; ensure a placeholder if not provided.
        $ownerEmail = ($email !== null && $email !== '') ? $email : 'owner@invalid.local';

        $lead = new Lead($city, $service, $fullName, $ownerEmail);
        $lead->setPhone($phone);
        $lead->setPetType($petType);
        $lead->setBreedSize($breedSize);
        $lead->setConsentToShare($consent);

        $this->leadTokenFactory->issueOwnerToken($lead);

        $this->em->persist($lead);
        $this->em->flush();

        $this->bus->dispatch(new DispatchLeadMessage((int) $lead->getId()));

        return $this->render('lead/thank_you.html.twig', [
            'city' => $city,
            'service' => $service,
            'lead' => $lead,
        ]);
    }

    private function n(?string $v): ?string
    {
        if ($v === null) return null;
        $t = trim($v);
        return $t === '' ? '' : $t;
    }

    private function isValidPhone(string $phone): bool
    {
        $digits = preg_replace('/[^0-9]/', '', $phone) ?? '';
        return strlen($digits) >= 7; // basic sanity check
    }
}

