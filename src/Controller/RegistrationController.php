<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedRole = $form->get('role')->getData();
            if (!\is_string($selectedRole)) {
                throw $this->createAccessDeniedException('Invalid role');
            }

            $allowedRoles = ['ROLE_OWNER', User::ROLE_GROOMER];
            if (!\in_array($selectedRole, $allowedRoles, true)) {
                throw $this->createAccessDeniedException('Invalid role');
            }

            $user->setRoles([$selectedRole, 'ROLE_USER']);
            $plainPassword = $form->get('plainPassword')->getData();
            if (!\is_string($plainPassword)) {
                throw new \RuntimeException('Invalid password');
            }

            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            // Log the user in immediately after registration
            // Requires at least one authenticator configured on the firewall (form_login enabled)
            $security->login($user);

            // After successful registration, if user registered as a groomer and
            // we have a stored lead claim intent, redirect to re-attempt the claim
            $session = $request->getSession();
            $intent = $session->get('lead_claim_intent');
            if ($selectedRole === User::ROLE_GROOMER && \is_array($intent)) {
                $claimUrl = (string) ($intent['claim_url'] ?? '');
                if ($claimUrl !== '') {
                    // Check expiration from query (if provided)
                    $qs = (string) parse_url($claimUrl, PHP_URL_QUERY);
                    $params = [];
                    if ($qs !== '') {
                        parse_str($qs, $params);
                    }
                    $exp = isset($params['exp']) && ctype_digit((string) $params['exp']) ? (int) $params['exp'] : null;
                    $stillValid = $exp === null || $exp >= time();

                    // Clear the intent to avoid loops
                    $session->remove('lead_claim_intent');

                    if ($stillValid) {
                        return $this->redirect($claimUrl);
                    }
                }
            }

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
