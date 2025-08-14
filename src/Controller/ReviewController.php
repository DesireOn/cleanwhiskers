<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Review;
use App\Entity\User;
use App\Form\ReviewFormType;
use App\Repository\GroomerProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ReviewController extends AbstractController
{
    #[Route('/groomers/{slug}/reviews/new', name: 'app_review_new', methods: ['GET', 'POST'], requirements: ['slug' => '[^/]+-[^/]+'])]
    public function new(
        string $slug,
        Request $request,
        GroomerProfileRepository $groomerRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $groomer = $groomerRepository->findOneBySlug($slug);
        if (null === $groomer) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ReviewFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ratingData = $form->get('rating')->getData();
            $commentData = $form->get('comment')->getData();

            if (!\is_int($ratingData) || !\is_string($commentData)) {
                throw new \RuntimeException('Invalid form data');
            }

            $comment = strip_tags($commentData);

            $user = $this->getUser();
            if (!$user instanceof User) {
                throw $this->createAccessDeniedException();
            }

            $review = new Review($groomer, $user, $ratingData, $comment);
            $entityManager->persist($review);
            $entityManager->flush();

            return $this->redirectToRoute('app_groomer_show', ['slug' => $slug]);
        }

        return $this->render('review/new.html.twig', [
            'reviewForm' => $form->createView(),
            'groomer' => $groomer,
        ]);
    }
}
