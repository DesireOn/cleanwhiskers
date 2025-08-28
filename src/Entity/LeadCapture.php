<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'lead_capture')]
class LeadCapture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(name: 'dog_breed', length: 255, nullable: true)]
    private ?string $dogBreed = null;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private City $city;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private Service $service;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $name, string $email, City $city, Service $service)
    {
        $this->name = $name;
        $this->email = $email;
        $this->city = $city;
        $this->service = $service;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getDogBreed(): ?string
    {
        return $this->dogBreed;
    }

    public function setDogBreed(?string $dogBreed): void
    {
        $this->dogBreed = $dogBreed;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
