<?php

declare(strict_types=1);

namespace App\Entity\Blog;

use App\Domain\Shared\SluggerTrait;
use App\Repository\BlogCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogCategoryRepository::class)]
#[ORM\Table(name: 'blog_category')]
#[ORM\Index(name: 'idx_blog_category_slug', fields: ['slug'])]
class BlogCategory
{
    use SluggerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
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
}
