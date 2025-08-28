<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\LeadCapture;
use App\Repository\CityRepository;
use App\Repository\LeadCaptureRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class LeadCaptureController extends AbstractController
{
    public function __construct(
        private readonly LeadCaptureRepository $repository,
        private readonly CityRepository $cityRepository,
        private readonly ServiceRepository $serviceRepository,
        private readonly RequestStack $requestStack,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/lead-capture', name: 'app_lead_capture', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        // Basic rate limiting: one submission per minute per session
        $session = $this->requestStack->getSession();
        $lastRaw = $session->get('lead_capture_time');
        $last = \is_numeric($lastRaw) ? (int) $lastRaw : 0;
        if (0 !== $last && (time() - $last) < 60) {
            return new JsonResponse(['error' => 'Too many requests'], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $data = json_decode($request->getContent(), true);
        if (!\is_array($data)) {
            $data = [];
        }
        $constraints = new Assert\Collection([
            'name' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            'email' => [new Assert\NotBlank(), new Assert\Email(), new Assert\Length(max: 255)],
            'dogBreed' => [new Assert\Optional(new Assert\Length(max: 255))],
            'city' => [new Assert\NotBlank(), new Assert\Positive()],
            'service' => [new Assert\NotBlank(), new Assert\Positive()],
            'website' => [new Assert\Blank()], // honeypot
        ]);

        $violations = $this->validator->validate($data, $constraints);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $cityId = isset($data['city']) && \is_numeric($data['city']) ? (int) $data['city'] : 0;
        $serviceId = isset($data['service']) && \is_numeric($data['service']) ? (int) $data['service'] : 0;
        $city = $this->cityRepository->find($cityId);
        $service = $this->serviceRepository->find($serviceId);
        if (null === $city || null === $service) {
            return new JsonResponse(['error' => 'Invalid city or service'], Response::HTTP_BAD_REQUEST);
        }

        $name = isset($data['name']) && \is_string($data['name']) ? $data['name'] : '';
        $email = isset($data['email']) && \is_string($data['email']) ? $data['email'] : '';
        $dogBreedValue = isset($data['dogBreed']) && \is_string($data['dogBreed']) ? $data['dogBreed'] : null;

        $lead = new LeadCapture(
            trim(strip_tags($name)),
            trim(strtolower($email)),
            $city,
            $service,
        );
        if (null !== $dogBreedValue && '' !== $dogBreedValue) {
            $lead->setDogBreed(trim(strip_tags($dogBreedValue)));
        }

        $this->repository->save($lead);
        $session->set('lead_capture_time', time());

        return new JsonResponse(['success' => true], Response::HTTP_CREATED);
    }
}
