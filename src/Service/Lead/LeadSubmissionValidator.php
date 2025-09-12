<?php

declare(strict_types=1);

namespace App\Service\Lead;

use App\Dto\Lead\LeadSubmissionDto;
use App\Repository\CityRepository;
use App\Repository\ServiceRepository;
use App\Service\Captcha\CaptchaVerifierInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class LeadSubmissionValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly CityRepository $cities,
        private readonly ServiceRepository $services,
        private readonly CaptchaVerifierInterface $captcha,
    ) {
    }

    /**
     * Validate input and return a flat list of error messages.
     *
     * @return list<string>
     */
    public function validate(LeadSubmissionDto $dto): array
    {
        $errors = [];

        // 1) Base field constraints
        $violations = $this->validator->validate($dto);
        $errors = array_merge($errors, $this->flattenViolations($violations));

        // 2) Honeypot must be empty
        if (null !== $dto->honeypot && trim($dto->honeypot) !== '') {
            $errors[] = 'Invalid submission.';
        }

        // 3) City and service must exist
        if ($dto->citySlug !== '' && null === $this->cities->findOneBySlug($dto->citySlug)) {
            $errors[] = 'Please select a valid city.';
        }
        if ($dto->serviceSlug !== '' && null === $this->services->findOneBySlug($dto->serviceSlug)) {
            $errors[] = 'Please select a valid service.';
        }

        // 4) Phone basic sanity: at least 7 digits
        if ($dto->phone !== '' && !$this->isValidPhone($dto->phone)) {
            $errors[] = 'Please provide a valid phone number.';
        }

        // 5) CAPTCHA verification
        if (!$this->captcha->verify($dto->captchaToken, $dto->clientIp)) {
            $errors[] = 'CAPTCHA verification failed.';
        }

        return array_values(array_unique($errors));
    }

    /**
     * @return list<string>
     */
    private function flattenViolations(ConstraintViolationListInterface $violations): array
    {
        $messages = [];
        foreach ($violations as $v) {
            $messages[] = $v->getMessage();
        }
        return $messages;
    }

    private function isValidPhone(string $phone): bool
    {
        $digits = preg_replace('/[^0-9]/', '', $phone) ?? '';
        return strlen($digits) >= 7;
    }
}
