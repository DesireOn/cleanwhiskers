<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BlogControllerTest extends WebTestCase
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

    public function testBlogIndexListsPosts(): void
    {
        $category = new BlogCategory('General');
        $category->refreshSlugFrom($category->getName());
        $post1 = new BlogPost($category, 'Post One', 'Ex1', '<p>One</p>');
        $post1->refreshSlugFrom($post1->getTitle());
        $post1->setIsPublished(true);
        $post1->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $post2 = new BlogPost($category, 'Post Two', 'Ex2', '<p>Two</p>');
        $post2->refreshSlugFrom($post2->getTitle());
        $post2->setIsPublished(true);
        $post2->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $this->em->persist($category);
        $this->em->persist($post1);
        $this->em->persist($post2);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/blog');

        self::assertResponseIsSuccessful();
        self::assertSame(2, $crawler->filter('li.post')->count());
        self::assertSelectorTextContains('h1', 'Blog');
    }

    public function testBlogShowDisplaysPostWithSeo(): void
    {
        $category = new BlogCategory('General');
        $category->refreshSlugFrom($category->getName());
        $post = new BlogPost($category, 'Welcome', 'Ex', '<p>Body</p>');
        $post->refreshSlugFrom($post->getTitle());
        $post->setIsPublished(true);
        $post->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $post->setMetaTitle('Custom Title');
        $post->setMetaDescription('Meta desc');
        $this->em->persist($category);
        $this->em->persist($post);
        $this->em->flush();

        $path = sprintf('/blog/%s/%s/%s', $post->getPublishedAt()->format('Y'), $post->getPublishedAt()->format('m'), $post->getSlug());
        $this->client->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Welcome');
        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('<title>Custom Title</title>', $content);
        self::assertStringContainsString('<meta name="description" content="Meta desc"', $content);
    }
}
