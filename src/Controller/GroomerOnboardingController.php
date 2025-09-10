<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\GroomerProfile;
use App\Entity\User;
use App\Form\GroomerProfileOnboardingType;
use App\Repository\GroomerProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GroomerOnboardingController extends AbstractController
{
    public function __construct(
        private readonly GroomerProfileRepository $profiles,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/groomer/onboarding', name: 'groomer_onboarding', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            // Not logged-in: go to login
            return $this->redirectToRoute('app_login');
        }

        if (!$user->isGroomer()) {
            // Require groomer role to create a groomer profile
            return $this->render('lead/claim_invalid.html.twig', [
                'reason' => 'needs_profile',
            ], new Response('', Response::HTTP_FORBIDDEN));
        }

        $existing = $this->profiles->findOneBy(['user' => $user]);
        if ($existing instanceof GroomerProfile) {
            // Already has a profile: go to its public page if possible
            return $this->redirectToRoute('app_groomer_show', ['slug' => $existing->getSlug()]);
        }

        $form = $this->createForm(GroomerProfileOnboardingType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var \App\Entity\City $city */
            $city = $data['city'];
            $businessName = (string) $data['businessName'];
            $about = (string) $data['about'];
            $phone = isset($data['phone']) && \is_string($data['phone']) ? trim($data['phone']) : null;
            $serviceArea = isset($data['serviceArea']) && \is_string($data['serviceArea']) ? trim($data['serviceArea']) : null;

            $profile = new GroomerProfile($user, $city, $businessName, $about);
            $profile->refreshSlugFrom($businessName);

            // Ensure unique slug by suffixing a counter if needed
            $baseSlug = $profile->getSlug();
            $slug = $baseSlug;
            $i = 2;
            while ($this->profiles->existsBySlug($slug)) {
                $slug = sprintf('%s-%d', $baseSlug, $i);
                $i++;
            }
            if ($slug !== $baseSlug) {
                $profile->refreshSlugFrom($slug);
            }

            if ($phone !== null && $phone !== '') {
                $profile->setPhone($phone);
            }
            if ($serviceArea !== null && $serviceArea !== '') {
                $profile->setServiceArea($serviceArea);
            }

            $this->em->persist($profile);
            $this->em->flush();

            // If we have a stored lead claim intent, redirect back to the claim URL
            $session = $request->getSession();
            $intent = $session->get('lead_claim_intent');
            if (\is_array($intent)) {
                $claimUrl = (string) ($intent['claim_url'] ?? '');
                if ($claimUrl !== '') {
                    $qs = (string) parse_url($claimUrl, PHP_URL_QUERY);
                    $params = [];
                    if ($qs !== '') {
                        parse_str($qs, $params);
                    }
                    $exp = isset($params['exp']) && ctype_digit((string) $params['exp']) ? (int) $params['exp'] : null;
                    $stillValid = $exp === null || $exp >= time();
                    $session->remove('lead_claim_intent');
                    if ($stillValid) {
                        return $this->redirect($claimUrl);
                    }
                }
            }

            // Otherwise, show the newly created profile
            return $this->redirectToRoute('app_groomer_show', ['slug' => $profile->getSlug()]);
        }

        return $this->render('groomer/onboarding.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

