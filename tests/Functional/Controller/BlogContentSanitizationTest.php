<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BlogContentSanitizationTest extends WebTestCase
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

    public function testSanitizedContentRenderedWithoutScripts(): void
    {
        $category = new BlogCategory('News');
        $category->refreshSlugFrom($category->getName());
        $html = '<p onclick="alert(1)">Hi<script>alert(1)</script><code>echo 1;</code></p>';
        $post = new BlogPost($category, 'Sanitize', 'Excerpt', $html);
        $post->refreshSlugFrom($post->getTitle());
        $post->setIsPublished(true);
        $post->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $this->em->persist($category);
        $this->em->persist($post);
        $this->em->flush();

        self::assertStringNotContainsString('onclick', $post->getContentHtml());
        self::assertStringNotContainsString('<script', $post->getContentHtml());
        self::assertNotNull($post->getReadingMinutes());

        $path = sprintf('/blog/%s/%s/%s', $post->getPublishedAt()->format('Y'), $post->getPublishedAt()->format('m'), $post->getSlug());
        $crawler = $this->client->request('GET', $path);
        self::assertResponseIsSuccessful();

        $section = $crawler->filter('section.content')->html();
        self::assertStringNotContainsString('onclick', $section);
        self::assertStringNotContainsString('<script', $section);
        self::assertStringContainsString('<code>echo 1;</code>', $section);

        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('min read', $content);
    }
}
