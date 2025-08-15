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

    public function testCategoryPageRendersArticles(): void
    {
        $category = new BlogCategory('News');
        $category->refreshSlugFrom($category->getName());
        $post = new BlogPost($category, 'Cat Post', 'Ex', '<p>Body</p>');
        $post->refreshSlugFrom($post->getTitle());
        $post->setIsPublished(true);
        $post->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $this->em->persist($category);
        $this->em->persist($post);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/blog/category/'.$category->getSlug());
        self::assertResponseIsSuccessful();
        self::assertSame(1, $crawler->filter('article')->count());
    }

    public function testTagPageRendersArticles(): void
    {
        $category = new BlogCategory('News');
        $category->refreshSlugFrom($category->getName());
        $tag = new BlogTag('Tips');
        $tag->refreshSlugFrom($tag->getName());
        $post = new BlogPost($category, 'Tagged Post', 'Ex', '<p>Body</p>');
        $post->refreshSlugFrom($post->getTitle());
        $post->setIsPublished(true);
        $post->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $post->getTags()->add($tag);
        $this->em->persist($category);
        $this->em->persist($tag);
        $this->em->persist($post);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/blog/tag/'.$tag->getSlug());
        self::assertResponseIsSuccessful();
        self::assertSame(1, $crawler->filter('article')->count());
    }
}
