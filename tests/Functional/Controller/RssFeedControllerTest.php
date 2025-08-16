<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RssFeedControllerTest extends WebTestCase
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

    public function testFeedReturnsLatestPublishedPosts(): void
    {
        gc_enable();
        $category = new BlogCategory('News');
        $category->refreshSlugFrom($category->getName());
        $this->em->persist($category);

        for ($i = 0; $i < 51; ++$i) {
            $post = new BlogPost($category, 'Post '.$i, 'Excerpt', '<p>Content</p>');
            $post->refreshSlugFrom($post->getTitle());
            $post->setIsPublished(true);
            $post->setPublishedAt(new \DateTimeImmutable(sprintf('-%d days', $i + 1)));
            $post->setUpdatedAt(new \DateTimeImmutable(sprintf('-%d days', $i + 1)));
            $this->em->persist($post);
            $this->em->flush();
            $this->em->detach($post);
            unset($post);
        }
        $this->em->clear();

        $this->client->request('GET', '/feed.xml');
        self::assertResponseIsSuccessful();
        self::assertSame('application/rss+xml; charset=UTF-8', $this->client->getResponse()->headers->get('content-type'));

        $content = $this->client->getInternalResponse()->getContent();
        $dom = new \DOMDocument();
        $dom->loadXML($content);
        $items = $dom->getElementsByTagName('item');
        self::assertSame(50, $items->length);
    }
}
