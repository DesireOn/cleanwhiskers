<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Shared\SluggerTrait;
use App\Repository\CityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CityRepository::class)]
#[ORM\Table(name: 'city')]
#[ORM\Index(name: 'idx_city_slug', fields: ['slug'])]
class City
{
    use SluggerTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(name: 'seo_intro', type: Types::TEXT, nullable: true)]
    private ?string $seoIntro = null;

    #[ORM\Column(name: 'coverage_notes', type: Types::TEXT, nullable: true)]
    private ?string $coverageNotes = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $name)
    {
        $this->name = $name;
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

    public function getSeoIntro(): ?string
    {
        return $this->seoIntro;
    }

    public function setSeoIntro(?string $seoIntro): void
    {
        $this->seoIntro = $seoIntro;
    }

    public function getCoverageNotes(): ?string
    {
        return $this->coverageNotes;
    }

    public function setCoverageNotes(?string $coverageNotes): void
    {
        $this->coverageNotes = $coverageNotes;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
