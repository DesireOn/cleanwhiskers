<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use App\Entity\Blog\BlogTag;
use App\Infrastructure\Doctrine\SlugListener;
use App\Repository\Blog\BlogCategoryRepository;
use App\Repository\Blog\BlogPostRepository;
use App\Repository\Blog\BlogPostSlugHistoryRepository;
use App\Repository\Blog\BlogTagRepository;
use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use PHPUnit\Framework\TestCase;

final class BlogPostTest extends TestCase
{
    public function testAccessorsAndSlugGenerated(): void
    {
        $category = new BlogCategory('News');
        $tag = new BlogTag('Updates');
        $post = new BlogPost($category, 'Hello World', 'Short', '<p>Body</p>');
        $post->addTag($tag);
        $post->setCoverImagePath('cover.jpg');
        $post->setCanonicalUrl('https://example.com');
        $post->setMetaTitle('Meta');
        $post->setMetaDescription('Description');
        $post->setReadingMinutes(5);
        $post->setIsPublished(true);
        $now = new \DateTimeImmutable('-1 day');
        $post->setPublishedAt($now);

        $listener = new SlugListener(
            $this->createMock(CityRepository::class),
            $this->createMock(ServiceRepository::class),
            $this->createMock(GroomerProfileRepository::class),
            $this->createMock(BlogCategoryRepository::class),
            $this->createMock(BlogTagRepository::class),
            $this->createMock(BlogPostRepository::class),
            $this->createMock(BlogPostSlugHistoryRepository::class),
        );
        $em = $this->createMock(EntityManagerInterface::class);
        $listener->prePersist(new PrePersistEventArgs($post, $em));

        self::assertSame('hello-world', $post->getSlug());
        self::assertSame($category, $post->getCategory());
        self::assertSame('Hello World', $post->getTitle());
        self::assertSame('Short', $post->getExcerpt());
        self::assertSame('<p>Body</p>', $post->getContentHtml());
        self::assertSame('cover.jpg', $post->getCoverImagePath());
        self::assertTrue($post->isPublished());
        self::assertSame($now, $post->getPublishedAt());
        self::assertSame('https://example.com', $post->getCanonicalUrl());
        self::assertSame('Meta', $post->getMetaTitle());
        self::assertSame('Description', $post->getMetaDescription());
        self::assertSame(5, $post->getReadingMinutes());
        self::assertCount(1, $post->getTags());
        self::assertSame($tag, $post->getTags()->first());
    }
}
