<?php

declare(strict_types=1);

namespace App\Entity\Blog;

use App\Domain\Shared\SluggerTrait;
use App\Repository\BlogPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
#[ORM\Table(name: 'blog_post')]
#[ORM\Index(name: 'idx_blog_post_slug', fields: ['slug'])]
class BlogPost
{
    use SluggerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: BlogCategory::class)]
    #[ORM\JoinColumn(nullable: false)]
    private BlogCategory $category;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $excerpt = null;

    #[ORM\Column(name: 'content_html', type: Types::TEXT)]
    private string $contentHtml;

    #[ORM\Column(name: 'cover_image_path', length: 255, nullable: true)]
    private ?string $coverImagePath = null;

    #[ORM\Column(name: 'is_published')]
    private bool $isPublished = false;

    #[ORM\Column(name: 'published_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(name: 'canonical_url', length: 255, nullable: true)]
    private ?string $canonicalUrl = null;

    #[ORM\Column(name: 'meta_title', length: 255, nullable: true)]
    private ?string $metaTitle = null;

    #[ORM\Column(name: 'meta_description', length: 255, nullable: true)]
    private ?string $metaDescription = null;

    #[ORM\Column(name: 'reading_minutes', type: Types::SMALLINT, nullable: true)]
    private ?int $readingMinutes = null;

    /** @var Collection<int, BlogTag> */
    #[ORM\ManyToMany(targetEntity: BlogTag::class, inversedBy: 'posts')]
    #[ORM\JoinTable(name: 'blog_post_blog_tag')]
    private Collection $tags;

    public function __construct(BlogCategory $category, string $title, string $excerpt, string $contentHtml)
    {
        $this->category = $category;
        $this->title = $title;
        $this->excerpt = $excerpt;
        $this->contentHtml = $contentHtml;
        $this->tags = new ArrayCollection();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): BlogCategory
    {
        return $this->category;
    }

    public function setCategory(BlogCategory $category): void
    {
        $this->category = $category;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): void
    {
        $this->excerpt = $excerpt;
    }

    public function getContentHtml(): string
    {
        return $this->contentHtml;
    }

    public function setContentHtml(string $contentHtml): void
    {
        $this->contentHtml = $contentHtml;
    }

    public function getCoverImagePath(): ?string
    {
        return $this->coverImagePath;
    }

    public function setCoverImagePath(?string $coverImagePath): void
    {
        $this->coverImagePath = $coverImagePath;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): void
    {
        $this->isPublished = $isPublished;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    public function setCanonicalUrl(?string $canonicalUrl): void
    {
        $this->canonicalUrl = $canonicalUrl;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getReadingMinutes(): ?int
    {
        return $this->readingMinutes;
    }

    public function setReadingMinutes(?int $readingMinutes): void
    {
        $this->readingMinutes = $readingMinutes;
    }

    /**
     * @return Collection<int, BlogTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(BlogTag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->getPosts()->add($this);
        }
    }

    public function removeTag(BlogTag $tag): void
    {
        if ($this->tags->removeElement($tag)) {
            $tag->getPosts()->removeElement($this);
        }
    }
}
