<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'email_suppression')]
#[ORM\UniqueConstraint(name: 'uniq_email_suppression_email', columns: ['email'])]
class EmailSuppression
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $email, ?string $reason = null)
    {
        $this->email = $email;
        $this->reason = $reason;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }

    public function getReason(): ?string { return $this->reason; }
    public function setReason(?string $reason): void { $this->reason = $reason; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}

