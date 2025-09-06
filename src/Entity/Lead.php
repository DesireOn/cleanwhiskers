<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LeadRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeadRepository::class)]
#[ORM\Table(name: 'lead')]
#[ORM\Index(name: 'idx_lead_city', columns: ['city_id'])]
#[ORM\Index(name: 'idx_lead_service', columns: ['service_id'])]
#[ORM\Index(name: 'idx_lead_status', columns: ['status'])]
class Lead
{
    public const STATUS_NEW = 'new';
    public const STATUS_CLAIMED = 'claimed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const BILLING_UNBILLED = 'unbilled';
    public const BILLING_WAIVED = 'waived';
    public const BILLING_PAID = 'paid';

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

    // Enum-like string, e.g. dog/cat/etc.
    #[ORM\Column(name: 'pet_type', length: 32)]
    private string $petType;

    #[ORM\Column(name: 'breed_size', length: 32, nullable: true)]
    private ?string $breedSize = null;

    #[ORM\Column(name: 'owner_name', length: 255)]
    private string $ownerName;

    #[ORM\Column(name: 'owner_phone', length: 32)]
    private string $ownerPhone;

    #[ORM\Column(name: 'owner_email', length: 255, nullable: true)]
    private ?string $ownerEmail = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_NEW;

    #[ORM\Column(name: 'claim_token', length: 64, unique: true)]
    private string $claimToken;

    #[ORM\Column(name: 'claimed_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $claimedAt = null;

    #[ORM\ManyToOne(targetEntity: GroomerProfile::class)]
    #[ORM\JoinColumn(name: 'claimed_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?GroomerProfile $claimedBy = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'claim_fee_cents', type: Types::INTEGER, options: ['default' => 0])]
    private int $claimFeeCents = 0;

    #[ORM\Column(name: 'billing_status', length: 20, options: ['default' => self::BILLING_UNBILLED])]
    private string $billingStatus = self::BILLING_UNBILLED;

    public function __construct(
        City $city,
        Service $service,
        string $petType,
        string $ownerName,
        string $ownerPhone,
        string $claimToken
    ) {
        $this->city = $city;
        $this->service = $service;
        $this->petType = $petType;
        $this->ownerName = $ownerName;
        $this->ownerPhone = $ownerPhone;
        $this->claimToken = $claimToken;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function getPetType(): string
    {
        return $this->petType;
    }

    public function setPetType(string $petType): self
    {
        $this->petType = $petType;
        return $this;
    }

    public function getBreedSize(): ?string
    {
        return $this->breedSize;
    }

    public function setBreedSize(?string $breedSize): self
    {
        $this->breedSize = $breedSize;
        return $this;
    }

    public function getOwnerName(): string
    {
        return $this->ownerName;
    }

    public function setOwnerName(string $ownerName): self
    {
        $this->ownerName = $ownerName;
        return $this;
    }

    public function getOwnerPhone(): string
    {
        return $this->ownerPhone;
    }

    public function setOwnerPhone(string $ownerPhone): self
    {
        $this->ownerPhone = $ownerPhone;
        return $this;
    }

    public function getOwnerEmail(): ?string
    {
        return $this->ownerEmail;
    }

    public function setOwnerEmail(?string $ownerEmail): self
    {
        $this->ownerEmail = $ownerEmail;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $allowed = [self::STATUS_NEW, self::STATUS_CLAIMED, self::STATUS_EXPIRED, self::STATUS_CANCELLED];
        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid status.');
        }
        $this->status = $status;
        return $this;
    }

    public function getClaimToken(): string
    {
        return $this->claimToken;
    }

    public function getClaimedAt(): ?\DateTimeImmutable
    {
        return $this->claimedAt;
    }

    public function setClaimedAt(?\DateTimeImmutable $claimedAt): self
    {
        $this->claimedAt = $claimedAt;
        return $this;
    }

    public function getClaimedBy(): ?GroomerProfile
    {
        return $this->claimedBy;
    }

    public function setClaimedBy(?GroomerProfile $claimedBy): self
    {
        $this->claimedBy = $claimedBy;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getClaimFeeCents(): int
    {
        return $this->claimFeeCents;
    }

    public function setClaimFeeCents(int $claimFeeCents): self
    {
        $this->claimFeeCents = $claimFeeCents;
        return $this;
    }

    public function getBillingStatus(): string
    {
        return $this->billingStatus;
    }

    public function setBillingStatus(string $billingStatus): self
    {
        $allowed = [self::BILLING_UNBILLED, self::BILLING_WAIVED, self::BILLING_PAID];
        if (!in_array($billingStatus, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid billing status.');
        }
        $this->billingStatus = $billingStatus;
        return $this;
    }
}

