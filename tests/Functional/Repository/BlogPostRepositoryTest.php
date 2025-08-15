<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use App\Entity\Blog\BlogTag;
use App\Repository\BlogPostRepository;
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

    public function testFindPublishedExcludesDraftsAndFuture(): void
    {
        $category = new BlogCategory('General');
        $post1 = new BlogPost($category, 'Published', 'Ex', '<p>Published</p>');
        $post1->setIsPublished(true);
        $post1->setPublishedAt(new \DateTimeImmutable('-1 day'));

        $post2 = new BlogPost($category, 'Draft', 'Ex', '<p>Draft</p>');
        $post2->setIsPublished(false);
        $post2->setPublishedAt(new \DateTimeImmutable('-1 day'));

        $post3 = new BlogPost($category, 'Future', 'Ex', '<p>Future</p>');
        $post3->setIsPublished(true);
        $post3->setPublishedAt(new \DateTimeImmutable('+1 day'));

        $this->em->persist($category);
        $this->em->persist($post1);
        $this->em->persist($post2);
        $this->em->persist($post3);
        $this->em->flush();
        $this->em->clear();

        $result = $this->repository->findPublished();
        self::assertCount(1, $result);
        self::assertSame('Published', $result[0]->getTitle());
    }

    public function testFindByCategory(): void
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

        $catA = $this->em->getRepository(BlogCategory::class)->findOneBy(['name' => 'CatA']);
        $result = $this->repository->findPublishedByCategory($catA);
        self::assertCount(1, $result);
        self::assertSame('PostA', $result[0]->getTitle());
    }

    public function testFindByTag(): void
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

        $tagA = $this->em->getRepository(BlogTag::class)->findOneBy(['name' => 'TagA']);
        $result = $this->repository->findPublishedByTag($tagA);
        self::assertCount(1, $result);
        self::assertSame('Post1', $result[0]->getTitle());
    }
}
