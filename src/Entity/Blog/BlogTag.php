<?php

declare(strict_types=1);

namespace App\Entity\Blog;

use App\Domain\Shared\SluggerTrait;
use App\Repository\BlogTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogTagRepository::class)]
#[ORM\Table(name: 'blog_tag')]
#[ORM\Index(name: 'idx_blog_tag_slug', fields: ['slug'])]
class BlogTag
{
    use SluggerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    /** @var Collection<int, BlogPost> */
    #[ORM\ManyToMany(targetEntity: BlogPost::class, mappedBy: 'tags')]
    private Collection $posts;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->posts = new ArrayCollection();
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

    /**
     * @return Collection<int, BlogPost>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }
}
