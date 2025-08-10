<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'groomer_profile', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_groomer_slug', columns: ['slug'])
], indexes: [
    new ORM\Index(name: 'idx_groomer_primary_city', columns: ['primary_city_id']),
    new ORM\Index(name: 'idx_groomer_rating_avg', columns: ['rating_avg']),
    new ORM\Index(name: 'idx_groomer_is_verified', columns: ['is_verified'])
])]
class GroomerProfile
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $owner = null;

    #[ORM\Column(length: 150)]
    private string $name;

    #[ORM\Column(length: 170, unique: true)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column]
    private bool $isMobile = true;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $lat = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $lng = null;

    #[ORM\Column(type: Types::FLOAT, options: ['default' => 0])]
    private float $ratingAvg = 0.0;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $ratingCount = 0;

    #[ORM\ManyToOne(targetEntity: City::class)]
    private ?City $primaryCity = null;

    #[ORM\OneToMany(mappedBy: 'groomer', targetEntity: GroomerService::class, cascade: ['persist', 'remove'])]
    private Collection $services;

    #[ORM\OneToMany(mappedBy: 'groomer', targetEntity: Review::class, cascade: ['remove'])]
    private Collection $reviews;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;
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

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;
        return $this;
    }

    public function isMobile(): bool
    {
        return $this->isMobile;
    }

    public function setIsMobile(bool $isMobile): self
    {
        $this->isMobile = $isMobile;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function setLat(?string $lat): self
    {
        $this->lat = $lat;
        return $this;
    }

    public function getLng(): ?string
    {
        return $this->lng;
    }

    public function setLng(?string $lng): self
    {
        $this->lng = $lng;
        return $this;
    }

    public function getRatingAvg(): float
    {
        return $this->ratingAvg;
    }

    public function setRatingAvg(float $ratingAvg): self
    {
        $this->ratingAvg = $ratingAvg;
        return $this;
    }

    public function getRatingCount(): int
    {
        return $this->ratingCount;
    }

    public function setRatingCount(int $ratingCount): self
    {
        $this->ratingCount = $ratingCount;
        return $this;
    }

    public function getPrimaryCity(): ?City
    {
        return $this->primaryCity;
    }

    public function setPrimaryCity(?City $primaryCity): self
    {
        $this->primaryCity = $primaryCity;
        return $this;
    }

    /**
     * @return Collection<int, GroomerService>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(GroomerService $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setGroomer($this);
        }
        return $this;
    }

    public function removeService(GroomerService $service): self
    {
        if ($this->services->removeElement($service)) {
            if ($service->getGroomer() === $this) {
                $service->setGroomer($this);
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
            $review->setGroomer($this);
        }
        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getGroomer() === $this) {
                $review->setGroomer($this);
            }
        }
        return $this;
    }
}
