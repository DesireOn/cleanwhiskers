<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Shared\SluggerTrait;
use App\Repository\GroomerProfileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroomerProfileRepository::class)]
#[ORM\Table(name: 'groomer_profile')]
#[ORM\Index(name: 'idx_groomer_profile_slug', fields: ['slug'])]
class GroomerProfile
{
    use SluggerTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: City::class)]
    #[ORM\JoinColumn(nullable: false)]
    private City $city;

    #[ORM\Column(name: 'business_name', length: 255)]
    private string $businessName;

    #[ORM\Column(length: 255, unique: true)]
    private string $slug = '';

    #[ORM\Column(type: 'text')]
    private string $about;

    public function __construct(User $user, City $city, string $businessName, string $about, string $slug = '')
    {
        if (!in_array(User::ROLE_GROOMER, $user->getRoles(), true)) {
            throw new \InvalidArgumentException('User must have groomer role.');
        }

        $this->user = $user;
        $this->city = $city;
        $this->businessName = $businessName;
        $this->about = $about;
        $this->slug = $slug;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getAbout(): string
    {
        return $this->about;
    }
}
