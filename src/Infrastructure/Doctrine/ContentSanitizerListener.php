<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use App\Entity\Blog\BlogPost;
use App\Service\ContentSanitizer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

final class ContentSanitizerListener
{
    public function __construct(private readonly ContentSanitizer $sanitizer)
    {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof BlogPost) {
            $this->sanitizePost($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof BlogPost) {
            $this->sanitizePost($entity);

            /** @var EntityManagerInterface $em */
            $em = $args->getObjectManager();
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet(
                $em->getClassMetadata(BlogPost::class),
                $entity,
            );
        }
    }

    private function sanitizePost(BlogPost $post): void
    {
        $sanitized = $this->sanitizer->sanitize($post->getContentHtml());
        $post->setContentHtml($sanitized);
        $post->setReadingMinutes($this->sanitizer->computeReadingMinutes($sanitized));
    }
}
