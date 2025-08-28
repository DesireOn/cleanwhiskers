<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Shared\SluggerTrait;
use App\Repository\GroomerProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroomerProfileRepository::class)]
#[ORM\Table(name: 'groomer_profile')]
#[ORM\Index(name: 'idx_groomer_profile_slug', fields: ['slug'])]
#[ORM\Index(name: 'idx_groomer_profile_city', fields: ['city'])]
class GroomerProfile
{
    use SluggerTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: City::class)]
    #[ORM\JoinColumn(nullable: false)]
    private City $city;

    #[ORM\Column(name: 'business_name', length: 255)]
    private string $businessName;

    #[ORM\Column(type: 'text')]
    private string $about;

    #[ORM\Column(name: 'service_area', length: 120, nullable: true)]
    private ?string $serviceArea = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(name: 'services_offered', type: 'text', nullable: true)]
    private ?string $servicesOffered = null;

    #[ORM\Column(name: 'price_range', length: 64, nullable: true)]
    private ?string $priceRange = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $price = null;

    /**
     * @var string[]|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $badges = null;

    /**
     * @var string[]|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $specialties = null;

    /** @var Collection<int, Service> */
    #[ORM\ManyToMany(targetEntity: Service::class)]
    #[ORM\JoinTable(name: 'groomer_profile_service')]
    private Collection $services;

    public function __construct(?User $user, City $city, string $businessName, string $about, string $slug = '')
    {
        if (null !== $user && !in_array(User::ROLE_GROOMER, $user->getRoles(), true)) {
            throw new \InvalidArgumentException('User must have groomer role.');
        }

        $this->user = $user;
        $this->city = $city;
        $this->businessName = $businessName;
        $this->about = $about;
        $this->slug = $slug;
        $this->services = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function getBusinessName(): string
    {
        return $this->businessName;
    }

    public function getAbout(): string
    {
        return $this->about;
    }

    public function getServiceArea(): ?string
    {
        return $this->serviceArea;
    }

    public function setServiceArea(?string $serviceArea): self
    {
        $this->serviceArea = $serviceArea;

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

    public function getServicesOffered(): ?string
    {
        return $this->servicesOffered;
    }

    public function setServicesOffered(?string $servicesOffered): self
    {
        $this->servicesOffered = $servicesOffered;

        return $this;
    }

    public function getPriceRange(): ?string
    {
        return $this->priceRange;
    }

    public function setPriceRange(?string $priceRange): self
    {
        $this->priceRange = $priceRange;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getBadges(): array
    {
        return $this->badges ?? [];
    }

    /**
     * @param string[] $badges
     */
    public function setBadges(array $badges): self
    {
        $this->badges = $badges;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getSpecialties(): array
    {
        return $this->specialties ?? [];
    }

    /**
     * @param string[] $specialties
     */
    public function setSpecialties(array $specialties): self
    {
        $this->specialties = $specialties;

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        $this->services->removeElement($service);

        return $this;
    }
}
