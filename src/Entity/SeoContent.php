<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SeoContentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeoContentRepository::class)]
#[ORM\Table(name: 'seo_content')]
#[ORM\UniqueConstraint(name: 'uniq_seo_city_service', columns: ['city_id', 'service_id'])]
class SeoContent
{
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

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(name: 'image_path', length: 255, nullable: true)]
    private ?string $imagePath = null;

    public function __construct(City $city, Service $service, string $title, string $content)
    {
        $this->city = $city;
        $this->service = $service;
        $this->title = $title;
        $this->content = $content;
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        if (null !== $imagePath) {
            $imagePath = str_replace(['..', '\\'], '', $imagePath);
            $imagePath = ltrim($imagePath, '/\\');
        }
        $this->imagePath = $imagePath;

        return $this;
    }
}
