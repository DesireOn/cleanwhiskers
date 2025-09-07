<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'lead_capture')]
class Lead
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CLAIMED = 'claimed';
    public const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: City::class)]
    #[ORM\JoinColumn(nullable: false)]
    private City $city;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Service $service;

    #[ORM\Column(name: 'name', length: 255)]
    private string $fullName;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(name: 'pet_type', length: 50, nullable: true)]
    private ?string $petType = null;

    // Map to existing legacy column dog_breed
    #[ORM\Column(name: 'dog_breed', length: 255, nullable: true)]
    private ?string $breedSize = null;

    #[ORM\Column(name: 'consent_to_share', type: Types::BOOLEAN, options: ['default' => false])]
    private bool $consentToShare = false;

    #[ORM\Column(length: 20, options: ['default' => self::STATUS_PENDING])]
    private string $status = self::STATUS_PENDING;

    #[ORM\ManyToOne(targetEntity: GroomerProfile::class)]
    #[ORM\JoinColumn(name: 'claimed_by_id', referencedColumnName: 'id', nullable: true)]
    private ?GroomerProfile $claimedBy = null;

    #[ORM\Column(name: 'claimed_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $claimedAt = null;

    #[ORM\Column(name: 'owner_token_hash', length: 255, nullable: true)]
    private ?string $ownerTokenHash = null;

    #[ORM\Column(name: 'owner_token_expires_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $ownerTokenExpiresAt = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct(City $city, Service $service, string $fullName, string $email)
    {
        $this->city = $city;
        $this->service = $service;
        $this->fullName = $fullName;
        $this->email = $email;
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int { return $this->id; }

    public function getCity(): City { return $this->city; }
    public function getService(): Service { return $this->service; }

    public function getFullName(): string { return $this->fullName; }
    public function setFullName(string $fullName): void { $this->fullName = $fullName; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): void { $this->phone = $phone; }

    public function getPetType(): ?string { return $this->petType; }
    public function setPetType(?string $petType): void { $this->petType = $petType; }

    public function getBreedSize(): ?string { return $this->breedSize; }
    public function setBreedSize(?string $breedSize): void { $this->breedSize = $breedSize; }

    public function hasConsentToShare(): bool { return $this->consentToShare; }
    public function setConsentToShare(bool $consent): void { $this->consentToShare = $consent; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function getClaimedBy(): ?GroomerProfile { return $this->claimedBy; }
    public function setClaimedBy(?GroomerProfile $groomer): void { $this->claimedBy = $groomer; }

    public function getClaimedAt(): ?\DateTimeImmutable { return $this->claimedAt; }
    public function setClaimedAt(?\DateTimeImmutable $claimedAt): void { $this->claimedAt = $claimedAt; }

    public function getOwnerTokenHash(): ?string { return $this->ownerTokenHash; }
    public function setOwnerTokenHash(?string $hash): void { $this->ownerTokenHash = $hash; }

    public function getOwnerTokenExpiresAt(): ?\DateTimeImmutable { return $this->ownerTokenExpiresAt; }
    public function setOwnerTokenExpiresAt(?\DateTimeImmutable $at): void { $this->ownerTokenExpiresAt = $at; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}

