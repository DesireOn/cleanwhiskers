<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use App\Entity\Blog\BlogTag;
use App\Repository\Blog\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class BlogPostRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private BlogPostRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        $this->repository = $this->em->getRepository(BlogPost::class);
    }

    public function testFindPublishedPaginatesAndExcludesFuture(): void
    {
        $category = new BlogCategory('General');
        $posts = [];
        for ($i = 1; $i <= 3; ++$i) {
            $post = new BlogPost($category, 'Post'.$i, 'Ex', '<p>'.$i.'</p>');
            $post->setIsPublished(true);
            $post->setPublishedAt(new \DateTimeImmutable(sprintf('-%d day', 4 - $i)));
            $posts[] = $post;
        }
        $draft = new BlogPost($category, 'Draft', 'Ex', '<p>Draft</p>');
        $draft->setIsPublished(false);
        $draft->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $future = new BlogPost($category, 'Future', 'Ex', '<p>Future</p>');
        $future->setIsPublished(true);
        $future->setPublishedAt(new \DateTimeImmutable('+1 day'));

        $this->em->persist($category);
        foreach (array_merge($posts, [$draft, $future]) as $post) {
            $this->em->persist($post);
        }
        $this->em->flush();
        $this->em->clear();

        $page1 = $this->repository->findPublished(1, 2);
        self::assertCount(2, $page1);
        self::assertSame('Post3', $page1[0]['title']);
        self::assertSame('Post2', $page1[1]['title']);

        $page2 = $this->repository->findPublished(2, 2);
        self::assertCount(1, $page2);
        self::assertSame('Post1', $page2[0]['title']);
    }

    public function testFindByCategorySlug(): void
    {
        $catA = new BlogCategory('CatA');
        $catB = new BlogCategory('CatB');

        $postA = new BlogPost($catA, 'PostA', 'Ex', '<p>A</p>');
        $postA->setIsPublished(true);
        $postA->setPublishedAt(new \DateTimeImmutable('-1 day'));

        $postB = new BlogPost($catB, 'PostB', 'Ex', '<p>B</p>');
        $postB->setIsPublished(true);
        $postB->setPublishedAt(new \DateTimeImmutable('-1 day'));

        $this->em->persist($catA);
        $this->em->persist($catB);
        $this->em->persist($postA);
        $this->em->persist($postB);
        $this->em->flush();
        $this->em->clear();

        $result = $this->repository->findByCategorySlug($catA->getSlug(), 1, 10);
        self::assertCount(1, $result);
        self::assertSame('PostA', $result[0]['title']);
        self::assertSame($catA->getSlug(), $result[0]['category_slug']);
    }

    public function testFindByTagSlug(): void
    {
        $category = new BlogCategory('Cat');
        $tagA = new BlogTag('TagA');
        $tagB = new BlogTag('TagB');

        $post1 = new BlogPost($category, 'Post1', 'Ex', '<p>1</p>');
        $post1->setIsPublished(true);
        $post1->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $post1->addTag($tagA);

        $post2 = new BlogPost($category, 'Post2', 'Ex', '<p>2</p>');
        $post2->setIsPublished(true);
        $post2->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $post2->addTag($tagB);

        $this->em->persist($category);
        $this->em->persist($tagA);
        $this->em->persist($tagB);
        $this->em->persist($post1);
        $this->em->persist($post2);
        $this->em->flush();
        $this->em->clear();

        $result = $this->repository->findByTagSlug($tagA->getSlug(), 1, 10);
        self::assertCount(1, $result);
        self::assertSame('Post1', $result[0]['title']);
    }

    public function testFindLatest(): void
    {
        $category = new BlogCategory('General');
        for ($i = 1; $i <= 3; ++$i) {
            $post = new BlogPost($category, 'P'.$i, 'Ex', '<p>'.$i.'</p>');
            $post->setIsPublished(true);
            $post->setPublishedAt(new \DateTimeImmutable(sprintf('-%d day', 3 - $i)));
            $this->em->persist($post);
        }
        $this->em->persist($category);
        $this->em->flush();
        $this->em->clear();

        $latest = $this->repository->findLatest(2);
        self::assertCount(2, $latest);
        self::assertSame('P3', $latest[0]['title']);
        self::assertSame('P2', $latest[1]['title']);
    }
}
