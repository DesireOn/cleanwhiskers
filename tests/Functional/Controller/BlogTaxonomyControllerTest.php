<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use App\Entity\Blog\BlogTag;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class BlogTaxonomyControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testCategoryListsPosts(): void
    {
        $category = new BlogCategory('News');
        $category->refreshSlugFrom($category->getName());
        $post = new BlogPost($category, 'Category Post', 'Excerpt', '<p>Content</p>');
        $post->refreshSlugFrom($post->getTitle());
        $post->setIsPublished(true);
        $post->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $this->em->persist($category);
        $this->em->persist($post);
        $this->em->flush();

        $this->client->request('GET', '/blog/category/'.$category->getSlug());
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Category Post', (string) $this->client->getResponse()->getContent());
    }

    public function testCategoryRedirectsWhenSlugCasingMismatch(): void
    {
        $category = new BlogCategory('Tech');
        $category->refreshSlugFrom($category->getName());
        $this->em->persist($category);
        $this->em->flush();

        $this->client->request('GET', '/blog/category/'.strtoupper($category->getSlug()));
        self::assertResponseRedirects('/blog/category/'.$category->getSlug(), Response::HTTP_MOVED_PERMANENTLY);
    }

    public function testTagListsPosts(): void
    {
        $category = new BlogCategory('News');
        $category->refreshSlugFrom($category->getName());
        $tag = new BlogTag('Grooming');
        $tag->refreshSlugFrom($tag->getName());
        $post = new BlogPost($category, 'Tag Post', 'Excerpt', '<p>Content</p>');
        $post->refreshSlugFrom($post->getTitle());
        $post->setIsPublished(true);
        $post->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $post->addTag($tag);
        $this->em->persist($category);
        $this->em->persist($tag);
        $this->em->persist($post);
        $this->em->flush();

        $this->client->request('GET', '/blog/tag/'.$tag->getSlug());
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Tag Post', (string) $this->client->getResponse()->getContent());
    }

    public function testTagRedirectsWhenSlugCasingMismatch(): void
    {
        $tag = new BlogTag('Pets');
        $tag->refreshSlugFrom($tag->getName());
        $this->em->persist($tag);
        $this->em->flush();

        $this->client->request('GET', '/blog/tag/'.strtoupper($tag->getSlug()));
        self::assertResponseRedirects('/blog/tag/'.$tag->getSlug(), Response::HTTP_MOVED_PERMANENTLY);
    }
}
