<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\BookingRequestRepository::class)]
class BookingRequest
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bookingRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $petOwner = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?GroomerProfile $groomer = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(callback: [BookingRequest::class, 'getAvailableStatuses'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $message = '';

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $preferredAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'bookingRequest', targetEntity: Review::class)]
    private \Doctrine\Common\Collections\Collection $reviews;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->reviews = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPetOwner(): ?User
    {
        return $this->petOwner;
    }

    public function setPetOwner(?User $petOwner): self
    {
        $this->petOwner = $petOwner;

        return $this;
    }

    public function getGroomer(): ?GroomerProfile
    {
        return $this->groomer;
    }

    public function setGroomer(?GroomerProfile $groomer): self
    {
        $this->groomer = $groomer;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public static function getAvailableStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_ACCEPTED, self::STATUS_DECLINED];
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getPreferredAt(): ?\DateTimeImmutable
    {
        return $this->preferredAt;
    }

    public function setPreferredAt(?\DateTimeImmutable $preferredAt): self
    {
        $this->preferredAt = $preferredAt;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<int, Review>
     */
    public function getReviews(): \Doctrine\Common\Collections\Collection
    {
        return $this->reviews;
    }
}
