<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

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

    private function createPublishedPost(string $title = 'My Post'): BlogPost
    {
        $category = new BlogCategory('News');
        $category->refreshSlugFrom($category->getName());
        $post = new BlogPost($category, $title, 'Excerpt', '<p>Content</p>');
        $post->refreshSlugFrom($post->getTitle());
        $post->setIsPublished(true);
        $post->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $this->em->persist($category);
        $this->em->persist($post);
        $this->em->flush();

        return $post;
    }

    public function testIndexDisplaysPublishedPost(): void
    {
        $this->createPublishedPost('Hello World');

        $crawler = $this->client->request('GET', '/blog');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Hello World', (string) $this->client->getResponse()->getContent());
        self::assertSame(1, $crawler->filter('article')->count());
    }

    public function testDetailShowsPost(): void
    {
        $post = $this->createPublishedPost('Detail Post');
        $path = sprintf('/blog/%s/%s/%s', $post->getPublishedAt()->format('Y'), $post->getPublishedAt()->format('m'), $post->getSlug());

        $crawler = $this->client->request('GET', $path);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Detail Post', (string) $this->client->getResponse()->getContent());
        self::assertSame(1, $crawler->filter('article')->count());
    }

    public function testDetailReturns404ForFuturePost(): void
    {
        $category = new BlogCategory('News');
        $category->refreshSlugFrom($category->getName());
        $post = new BlogPost($category, 'Future Post', 'Excerpt', '<p>Content</p>');
        $post->refreshSlugFrom($post->getTitle());
        $post->setIsPublished(true);
        $post->setPublishedAt(new \DateTimeImmutable('+1 day'));
        $this->em->persist($category);
        $this->em->persist($post);
        $this->em->flush();

        $path = sprintf('/blog/%s/%s/%s', $post->getPublishedAt()->format('Y'), $post->getPublishedAt()->format('m'), $post->getSlug());
        $this->client->request('GET', $path);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDetailRedirectsWhenSlugCasingMismatch(): void
    {
        $post = $this->createPublishedPost('Case Post');
        $path = sprintf('/blog/%s/%s/%s', $post->getPublishedAt()->format('Y'), $post->getPublishedAt()->format('m'), strtoupper($post->getSlug()));

        $this->client->request('GET', $path);
        self::assertResponseRedirects(sprintf('/blog/%s/%s/%s', $post->getPublishedAt()->format('Y'), $post->getPublishedAt()->format('m'), $post->getSlug()), Response::HTTP_MOVED_PERMANENTLY);
    }
}
