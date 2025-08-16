<?php

declare(strict_types=1);

namespace App\Tests\E2E\Blog;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HeadingStructureTest extends WebTestCase
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

    private function createPublishedPost(): BlogPost
    {
        $category = new BlogCategory('News');
        $category->refreshSlugFrom($category->getName());
        $post = new BlogPost($category, 'Heading Post', 'Ex', '<h2>Section</h2><p>Body</p>');
        $post->refreshSlugFrom($post->getTitle());
        $post->setIsPublished(true);
        $post->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $this->em->persist($category);
        $this->em->persist($post);
        $this->em->flush();

        return $post;
    }

    public function testIndexHeadingOutlineIsLogical(): void
    {
        $this->createPublishedPost();
        $crawler = $this->client->request('GET', '/blog');
        self::assertResponseIsSuccessful();

        $main = $crawler->filter('main');
        self::assertSame(1, $main->filter('h1')->count());

        $headings = $main->filter('h1, h2, h3, h4, h5, h6');
        $prevLevel = 1;
        foreach ($headings as $index => $node) {
            $level = (int) substr($node->nodeName, 1);
            if (0 === $index) {
                self::assertSame(1, $level);
            } else {
                self::assertLessThanOrEqual($prevLevel + 1, $level);
            }
            $prevLevel = $level;
        }
    }

    public function testDetailHeadingOutlineIsLogical(): void
    {
        $post = $this->createPublishedPost();
        $path = sprintf('/blog/%s/%s/%s', $post->getPublishedAt()->format('Y'), $post->getPublishedAt()->format('m'), $post->getSlug());

        $crawler = $this->client->request('GET', $path);
        self::assertResponseIsSuccessful();

        $main = $crawler->filter('main');
        self::assertSame(1, $main->filter('h1')->count());

        $headings = $main->filter('h1, h2, h3, h4, h5, h6');
        $prevLevel = 1;
        foreach ($headings as $index => $node) {
            $level = (int) substr($node->nodeName, 1);
            if (0 === $index) {
                self::assertSame(1, $level);
            } else {
                self::assertLessThanOrEqual($prevLevel + 1, $level);
            }
            $prevLevel = $level;
        }
    }
}
