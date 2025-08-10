<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'booking_request', indexes: [
    new ORM\Index(name: 'idx_booking_groomer', columns: ['groomer_id']),
    new ORM\Index(name: 'idx_booking_status', columns: ['status']),
    new ORM\Index(name: 'idx_booking_created_at', columns: ['created_at'])
])]
class BookingRequest
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GroomerProfile::class)]
    #[ORM\JoinColumn(nullable: false)]
    private GroomerProfile $groomer;

    #[ORM\ManyToOne(targetEntity: User::class, nullable: true)]
    private ?User $requester = null;

    #[ORM\ManyToOne(targetEntity: Service::class, nullable: true)]
    private ?Service $service = null;

    #[ORM\ManyToOne(targetEntity: City::class, nullable: true)]
    private ?City $city = null;

    #[ORM\Column(length: 120)]
    private string $name;

    #[ORM\Column(length: 180)]
    private string $email;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(length: 20, options: ['default' => 'new'])]
    private string $status = 'new';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroomer(): GroomerProfile
    {
        return $this->groomer;
    }

    public function setGroomer(GroomerProfile $groomer): self
    {
        $this->groomer = $groomer;
        return $this;
    }

    public function getRequester(): ?User
    {
        return $this->requester;
    }

    public function setRequester(?User $requester): self
    {
        $this->requester = $requester;
        return $this;
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

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;
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
}
