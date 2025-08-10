<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity('email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private string $password = '';

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: GroomerProfile::class, cascade: ['persist', 'remove'])]
    private ?GroomerProfile $groomerProfile = null;

    #[ORM\OneToMany(mappedBy: 'petOwner', targetEntity: BookingRequest::class)]
    private Collection $bookingRequests;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Review::class)]
    private Collection $reviews;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->bookingRequests = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->email ?? '';
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getGroomerProfile(): ?GroomerProfile
    {
        return $this->groomerProfile;
    }

    public function setGroomerProfile(?GroomerProfile $groomerProfile): self
    {
        $this->groomerProfile = $groomerProfile;

        return $this;
    }

    /**
     * @return Collection<int, BookingRequest>
     */
    public function getBookingRequests(): Collection
    {
        return $this->bookingRequests;
    }

    public function addBookingRequest(BookingRequest $bookingRequest): self
    {
        if (!$this->bookingRequests->contains($bookingRequest)) {
            $this->bookingRequests->add($bookingRequest);
            $bookingRequest->setPetOwner($this);
        }

        return $this;
    }

    public function removeBookingRequest(BookingRequest $bookingRequest): self
    {
        if ($this->bookingRequests->removeElement($bookingRequest)) {
            if ($bookingRequest->getPetOwner() === $this) {
                $bookingRequest->setPetOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setAuthor($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getAuthor() === $this) {
                $review->setAuthor(null);
            }
        }

        return $this;
    }
}
