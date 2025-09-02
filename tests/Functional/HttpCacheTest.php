<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class HttpCacheTest extends WebTestCase
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

    public function testDetailReturns304WithEtag(): void
    {
        $category = new BlogCategory('News');
        $category->refreshSlugFrom($category->getName());
        $post = new BlogPost($category, 'Cache Post', 'Ex', '<p>Body</p>');
        $post->refreshSlugFrom($post->getTitle());
        $post->setIsPublished(true);
        $post->setPublishedAt(new \DateTimeImmutable('-1 day'));
        $this->em->persist($category);
        $this->em->persist($post);
        $this->em->flush();

        $path = sprintf('/blog/%s/%s/%s', $post->getPublishedAt()->format('Y'), $post->getPublishedAt()->format('m'), $post->getSlug());

        $this->client->request('GET', $path);
        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertSame('public, s-maxage=600', $response->headers->get('Cache-Control'));
        $etag = $response->headers->get('ETag');
        self::assertNotEmpty($etag);
        self::assertNotEmpty($response->headers->get('Last-Modified'));

        $this->client->request('GET', $path, server: ['HTTP_IF_NONE_MATCH' => $etag]);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_MODIFIED);
    }
}
