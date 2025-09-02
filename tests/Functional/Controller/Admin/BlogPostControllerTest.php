<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BlogPostControllerTest extends WebTestCase
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

    public function testAdminCanCreatePost(): void
    {
        $admin = (new User())
            ->setEmail('admin@example.com')
            ->setPassword('pass')
            ->setRoles(['ROLE_ADMIN']);
        $category = new BlogCategory('News');
        $this->em->persist($admin);
        $this->em->persist($category);
        $this->em->flush();

        $this->client->loginUser($admin);
        $crawler = $this->client->request('GET', '/admin/blog/posts/new');
        $token = $crawler->filter('input[name="blog_post[_token]"]')->attr('value');

        $this->client->request('POST', '/admin/blog/posts/new', [
            'blog_post' => [
                '_token' => $token,
                'title' => 'Hello',
                'excerpt' => 'Ex',
                'contentHtml' => '<p>Hi<script>alert(1)</script></p>',
                'canonicalUrl' => '',
                'metaTitle' => '',
                'metaDescription' => '',
                'category' => $category->getId(),
                'tags' => 'Foo,Bar',
                'isPublished' => 1,
                'publishedAt' => '',
            ],
        ]);

        self::assertResponseRedirects();
        /** @var BlogPost|null $post */
        $post = $this->em->getRepository(BlogPost::class)->findOneBy(['title' => 'Hello']);
        self::assertNotNull($post);
        self::assertStringNotContainsString('<script', $post->getContentHtml());
        self::assertSame(2, $post->getTags()->count());
        self::assertNotNull($post->getPublishedAt());
    }

    public function testDuplicateTagsRejected(): void
    {
        $admin = (new User())
            ->setEmail('admin2@example.com')
            ->setPassword('pass')
            ->setRoles(['ROLE_ADMIN']);
        $category = new BlogCategory('Guides');
        $this->em->persist($admin);
        $this->em->persist($category);
        $this->em->flush();

        $this->client->loginUser($admin);
        $crawler = $this->client->request('GET', '/admin/blog/posts/new');
        $token = $crawler->filter('input[name="blog_post[_token]"]')->attr('value');

        $this->client->request('POST', '/admin/blog/posts/new', [
            'blog_post' => [
                '_token' => $token,
                'title' => 'Dup',
                'excerpt' => 'Ex',
                'contentHtml' => '<p>Hi</p>',
                'canonicalUrl' => '',
                'metaTitle' => '',
                'metaDescription' => '',
                'category' => $category->getId(),
                'tags' => 'Foo, foo',
                'isPublished' => 0,
                'publishedAt' => '',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame(0, $this->em->getRepository(BlogPost::class)->count([]));
    }

    public function testScheduledPostRequiresFutureDate(): void
    {
        $admin = (new User())
            ->setEmail('admin3@example.com')
            ->setPassword('pass')
            ->setRoles(['ROLE_ADMIN']);
        $category = new BlogCategory('Tips');
        $this->em->persist($admin);
        $this->em->persist($category);
        $this->em->flush();

        $this->client->loginUser($admin);
        $crawler = $this->client->request('GET', '/admin/blog/posts/new');
        $token = $crawler->filter('input[name="blog_post[_token]"]')->attr('value');

        $yesterday = (new \DateTimeImmutable('-1 day'))->format('Y-m-d\TH:i');
        $this->client->request('POST', '/admin/blog/posts/new', [
            'blog_post' => [
                '_token' => $token,
                'title' => 'Sched',
                'excerpt' => 'Ex',
                'contentHtml' => '<p>Hi</p>',
                'canonicalUrl' => '',
                'metaTitle' => '',
                'metaDescription' => '',
                'category' => $category->getId(),
                'tags' => '',
                'isPublished' => 1,
                'publishedAt' => $yesterday,
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame(0, $this->em->getRepository(BlogPost::class)->count([]));
    }
}
