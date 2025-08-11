<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BookingRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRequestRepository::class)]
#[ORM\Table(name: 'booking_request')]
#[ORM\Index(name: 'idx_booking_request_groomer', columns: ['groomer_id'])]
#[ORM\Index(name: 'idx_booking_request_pet_owner', columns: ['pet_owner_id'])]
#[ORM\Index(name: 'idx_booking_request_status', columns: ['status'])]
class BookingRequest
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GroomerProfile::class)]
    #[ORM\JoinColumn(nullable: false)]
    private GroomerProfile $groomer;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'pet_owner_id', referencedColumnName: 'id', nullable: false)]
    private User $petOwner;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Service $service = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(name: 'requested_at')]
    private \DateTimeImmutable $requestedAt;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function __construct(GroomerProfile $groomer, User $petOwner)
    {
        $this->groomer = $groomer;
        $this->petOwner = $petOwner;
        $this->requestedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroomer(): GroomerProfile
    {
        return $this->groomer;
    }

    public function getPetOwner(): User
    {
        return $this->petOwner;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $allowed = [self::STATUS_PENDING, self::STATUS_ACCEPTED, self::STATUS_DECLINED];
        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid status.');
        }

        $this->status = $status;

        return $this;
    }

    public function getRequestedAt(): \DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }
}
