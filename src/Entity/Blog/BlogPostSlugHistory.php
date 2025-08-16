<?php

declare(strict_types=1);

namespace App\Entity\Blog;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'blog_post_slugs')]
#[ORM\Index(name: 'idx_blog_post_slugs_slug', columns: ['slug'])]
class BlogPostSlugHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: BlogPost::class)]
    #[ORM\JoinColumn(name: 'post_id', nullable: false, onDelete: 'CASCADE')]
    private BlogPost $post;

    #[ORM\Column(length: 255, unique: true)]
    private string $slug;

    public function __construct(BlogPost $post, string $slug)
    {
        $this->post = $post;
        $this->slug = $slug;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): BlogPost
    {
        return $this->post;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }
}
