<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use App\EventSubscriber\BlogPostCacheSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class BlogPostCacheSubscriberTest extends TestCase
{
    public function testInvalidateOnUpdate(): void
    {
        $cache = $this->createMock(TagAwareCacheInterface::class);
        $cache->expects(self::once())->method('invalidateTags')->with(['blog_posts']);

        $subscriber = new BlogPostCacheSubscriber($cache);

        $post = new BlogPost(new BlogCategory('News'), 'Title', 'Ex', '<p>Body</p>');
        $em = $this->createMock(EntityManagerInterface::class);
        $args = new PostUpdateEventArgs($post, $em);

        $subscriber->postUpdate($args);
    }
}
