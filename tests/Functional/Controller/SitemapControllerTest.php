<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SitemapControllerTest extends WebTestCase
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

    public function testSitemapIncludesPublishedPostsAndCategories(): void
    {
        $category = new BlogCategory('News');
        $category->refreshSlugFrom($category->getName());

        $post = new BlogPost($category, 'Published Post', 'Excerpt', '<p>Content</p>');
        $post->refreshSlugFrom($post->getTitle());
        $post->setIsPublished(true);
        $post->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $post->setUpdatedAt(new \DateTimeImmutable('-1 day'));

        $draft = new BlogPost($category, 'Draft Post', 'Excerpt', '<p>Content</p>');
        $draft->refreshSlugFrom($draft->getTitle());

        $future = new BlogPost($category, 'Future Post', 'Excerpt', '<p>Content</p>');
        $future->refreshSlugFrom($future->getTitle());
        $future->setIsPublished(true);
        $future->setPublishedAt(new \DateTimeImmutable('+1 day'));
        $future->setUpdatedAt(new \DateTimeImmutable('+1 day'));

        $this->em->persist($category);
        $this->em->persist($post);
        $this->em->persist($draft);
        $this->em->persist($future);
        $this->em->flush();

        $this->client->request('GET', '/sitemap.xml');
        self::assertResponseIsSuccessful();
        self::assertSame('application/xml; charset=UTF-8', $this->client->getResponse()->headers->get('content-type'));

        $dom = new \DOMDocument();
        $dom->loadXML((string) $this->client->getResponse()->getContent());
        $urls = $dom->getElementsByTagName('url');
        self::assertSame(4, $urls->length);
    }
}
