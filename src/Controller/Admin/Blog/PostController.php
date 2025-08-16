<?php

declare(strict_types=1);

namespace App\Controller\Admin\Blog;

use App\Entity\Blog\BlogCategory;
use App\Entity\Blog\BlogPost;
use App\Entity\Blog\BlogTag;
use App\Form\Blog\BlogPostType;
use App\Repository\Blog\BlogCategoryRepository;
use App\Repository\Blog\BlogTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/blog/posts')]
final class PostController extends AbstractController
{
    #[Route('/new', name: 'admin_blog_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, BlogCategoryRepository $categoryRepo, BlogTagRepository $tagRepo): Response
    {
        $defaultCategory = $categoryRepo->findOneBy([]) ?? new BlogCategory('temp');
        $post = new BlogPost($defaultCategory, '', '', '');
        $form = $this->createForm(BlogPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $tagData = $form->get('tags')->getData();
            $tagsInput = is_string($tagData) ? $tagData : '';
            $tags = $this->parseTags($tagsInput);
            if ($this->hasDuplicateTags($tags)) {
                $form->get('tags')->addError(new FormError('Duplicate tag names are not allowed.'));
            }

            $this->handlePublishState($post, $form);

            if ($form->isValid()) {
                $this->syncTags($post, $tags, $tagRepo, $em);
                $em->persist($post);
                $em->flush();

                return $this->redirectToRoute('admin_blog_post_edit', ['id' => $post->getId()]);
            }
        }

        return $this->render('admin/blog/post_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_blog_post_edit', methods: ['GET', 'POST'])]
    public function edit(BlogPost $post, Request $request, EntityManagerInterface $em, BlogTagRepository $tagRepo): Response
    {
        $tagNames = [];
        foreach ($post->getTags() as $tag) {
            $tagNames[] = $tag->getName();
        }
        $form = $this->createForm(BlogPostType::class, $post);
        $form->get('tags')->setData(implode(', ', $tagNames));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $tagData = $form->get('tags')->getData();
            $tagsInput = is_string($tagData) ? $tagData : '';
            $tags = $this->parseTags($tagsInput);
            if ($this->hasDuplicateTags($tags)) {
                $form->get('tags')->addError(new FormError('Duplicate tag names are not allowed.'));
            }

            $this->handlePublishState($post, $form);

            if ($form->isValid()) {
                foreach ($post->getTags() as $existing) {
                    $post->removeTag($existing);
                }
                $this->syncTags($post, $tags, $tagRepo, $em);
                $em->flush();

                return $this->redirectToRoute('admin_blog_post_edit', ['id' => $post->getId()]);
            }
        }

        return $this->render('admin/blog/post_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return list<string>
     */
    private function parseTags(string $input): array
    {
        $parts = array_filter(array_map(static fn (string $v): string => trim($v), explode(',', $input)));

        return array_values($parts);
    }

    /**
     * @param list<string> $tags
     */
    private function hasDuplicateTags(array $tags): bool
    {
        $slugger = new AsciiSlugger();
        $slugs = [];
        foreach ($tags as $tag) {
            $slug = $slugger->slug(mb_strtolower($tag))->lower()->toString();
            if (in_array($slug, $slugs, true)) {
                return true;
            }
            $slugs[] = $slug;
        }

        return false;
    }

    /**
     * @param list<string> $tags
     */
    private function syncTags(BlogPost $post, array $tags, BlogTagRepository $tagRepo, EntityManagerInterface $em): void
    {
        $slugger = new AsciiSlugger();
        foreach ($tags as $tagName) {
            $slug = $slugger->slug(mb_strtolower($tagName))->lower()->toString();
            $tag = $tagRepo->findOneBy(['slug' => $slug]);
            if (null === $tag) {
                $tag = new BlogTag($tagName);
                $tag->refreshSlugFrom($tagName);
                $em->persist($tag);
            }
            $post->addTag($tag);
        }
    }

    private function handlePublishState(BlogPost $post, \Symfony\Component\Form\FormInterface $form): void
    {
        if ($post->isPublished()) {
            $now = new \DateTimeImmutable();
            $publishedAt = $post->getPublishedAt();
            if (null === $publishedAt) {
                $post->setPublishedAt($now);
            } elseif ($publishedAt <= $now) {
                $form->get('publishedAt')->addError(new FormError('Publish date must be in the future.'));
            }
        } else {
            $post->setPublishedAt(null);
        }
    }
}
