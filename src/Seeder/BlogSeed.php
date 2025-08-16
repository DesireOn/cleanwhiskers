<?php

declare(strict_types=1);

namespace App\Seeder;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use App\Entity\Blog\BlogTag;
use App\Repository\Blog\BlogCategoryRepository;
use App\Repository\Blog\BlogPostRepository;
use App\Repository\Blog\BlogTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class BlogSeed
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly BlogCategoryRepository $categoryRepository,
        private readonly BlogTagRepository $tagRepository,
        private readonly BlogPostRepository $postRepository,
    ) {
    }

    public function seed(): void
    {
        $data = self::demoData();
        $slugger = new AsciiSlugger();

        $this->em->wrapInTransaction(function () use ($data, $slugger): void {
            $categories = [];
            foreach ($data['categories'] as $name) {
                $slug = $slugger->slug(mb_strtolower($name))->lower()->toString();
                $category = $this->categoryRepository->findOneBy(['slug' => $slug]);
                if (null === $category) {
                    $category = new BlogCategory($name);
                    $category->refreshSlugFrom($name);
                    $this->em->persist($category);
                } else {
                    $category->setName($name);
                }
                $categories[$name] = $category;
            }

            $tags = [];
            foreach ($data['tags'] as $name) {
                $slug = $slugger->slug(mb_strtolower($name))->lower()->toString();
                $tag = $this->tagRepository->findOneBy(['slug' => $slug]);
                if (null === $tag) {
                    $tag = new BlogTag($name);
                    $tag->refreshSlugFrom($name);
                    $this->em->persist($tag);
                } else {
                    $tag->setName($name);
                }
                $tags[$name] = $tag;
            }

            foreach ($data['posts'] as $postData) {
                $slug = $slugger->slug(mb_strtolower($postData['title']))->lower()->toString();
                $post = $this->postRepository->findOneBy(['slug' => $slug]);

                if (null === $post) {
                    $category = $categories[$postData['category']];
                    $post = new BlogPost($category, $postData['title'], $postData['excerpt'], $postData['content_html']);
                    $post->refreshSlugFrom($postData['title']);
                    $this->em->persist($post);
                } else {
                    $post->setCategory($categories[$postData['category']]);
                    $post->setTitle($postData['title']);
                    $post->setExcerpt($postData['excerpt']);
                    $post->setContentHtml($postData['content_html']);
                }

                $post->setIsPublished($postData['is_published']);
                $post->setPublishedAt($postData['published_at']);

                foreach ($post->getTags() as $existing) {
                    $post->removeTag($existing);
                }
                foreach ($postData['tags'] as $tagName) {
                    $post->addTag($tags[$tagName]);
                }
            }

            $this->em->flush();
        });
    }

    /**
     * @return array{
     *     categories: list<string>,
     *     tags: list<string>,
     *     posts: list<array{
     *         title: string,
     *         excerpt: string,
     *         content_html: string,
     *         category: string,
     *         tags: list<string>,
     *         is_published: bool,
     *         published_at: \DateTimeImmutable,
     *     }>
     * }
     */
    private static function demoData(): array
    {
        return [
            'categories' => ['News'],
            'tags' => ['General', 'Update'],
            'posts' => [
                [
                    'title' => 'Welcome',
                    'excerpt' => 'Welcome to our blog',
                    'content_html' => '<p>First post</p>',
                    'category' => 'News',
                    'tags' => ['General', 'Update'],
                    'is_published' => true,
                    'published_at' => new \DateTimeImmutable('-1 day'),
                ],
            ],
        ];
    }
}
