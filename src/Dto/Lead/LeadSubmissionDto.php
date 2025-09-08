<?php

declare(strict_types=1);

namespace App\Dto\Lead;

use Symfony\Component\Validator\Constraints as Assert;

final class LeadSubmissionDto
{
    #[Assert\NotBlank(message: 'Please select a valid city.')]
    public string $citySlug = '';

    #[Assert\NotBlank(message: 'Please select a valid service.')]
    public string $serviceSlug = '';

    #[Assert\NotBlank(message: 'Full name is required.')]
    public string $fullName = '';

    #[Assert\NotBlank(message: 'Please provide a valid phone number.')]
    public string $phone = '';

    #[Assert\NotBlank(message: 'Please select your pet type.')]
    public string $petType = '';

    public string $breedSize = '';

    #[Assert\Email(message: 'Please provide a valid email address.')]
    public ?string $email = null;

    public bool $consentToShare = false;

    /** Simple honeypot field; should be empty */
    public ?string $honeypot = null;

    /** Captcha token; may be empty if captcha is disabled */
    public ?string $captchaToken = null;

    /** Client IP for captcha verification */
    public ?string $clientIp = null;
}

