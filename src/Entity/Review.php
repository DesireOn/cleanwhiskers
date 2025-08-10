<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'review', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_review_author_groomer', columns: ['author_id', 'groomer_id'])
], indexes: [
    new ORM\Index(name: 'idx_review_groomer', columns: ['groomer_id']),
    new ORM\Index(name: 'idx_review_created_at', columns: ['created_at']),
    new ORM\Index(name: 'idx_review_status', columns: ['status'])
])]
class Review
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GroomerProfile::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private GroomerProfile $groomer;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $author = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $rating;

    #[ORM\Column(length: 140, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $body = null;

    #[ORM\Column(length: 20, options: ['default' => 'pending'])]
    private string $status = 'pending';

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;
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
