<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BlogPaginationTest extends WebTestCase
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

    private function seedPosts(int $count): void
    {
        $category = new BlogCategory('News');
        $category->refreshSlugFrom($category->getName());
        $this->em->persist($category);

        for ($i = 1; $i <= $count; ++$i) {
            $post = new BlogPost($category, 'Post '.$i, 'Excerpt', '<p>Content</p>');
            $post->refreshSlugFrom($post->getTitle());
            $post->setIsPublished(true);
            $post->setPublishedAt(new \DateTimeImmutable(sprintf('-%d days', $i + 1)));
            $this->em->persist($post);
        }

        $this->em->flush();
    }

    public function testPaginationMeta(): void
    {
        $this->seedPosts(101);

        $crawler = $this->client->request('GET', '/blog');
        self::assertResponseIsSuccessful();
        self::assertSame('http://localhost/blog', $crawler->filter('link[rel="canonical"]')->attr('href'));
        self::assertSame('http://localhost/blog?page=2', $crawler->filter('link[rel="next"]')->attr('href'));
        self::assertSame(0, $crawler->filter('link[rel="prev"]')->count());
        self::assertSame(0, $crawler->filter('meta[name="robots"]')->count());

        $crawler = $this->client->request('GET', '/blog?page=11');
        self::assertResponseIsSuccessful();
        self::assertSame('http://localhost/blog?page=11', $crawler->filter('link[rel="canonical"]')->attr('href'));
        self::assertSame('http://localhost/blog?page=10', $crawler->filter('link[rel="prev"]')->attr('href'));
        self::assertSame(0, $crawler->filter('link[rel="next"]')->count());
        self::assertSame('noindex,follow', $crawler->filter('meta[name="robots"]')->attr('content'));
    }
}
