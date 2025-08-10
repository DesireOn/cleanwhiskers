<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\GroomerProfile;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

class SlugListener
{
    public function __construct(private SluggerInterface $slugger, private EntityManagerInterface $em)
    {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->handleEntity($args->getObject());
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->handleEntity($args->getObject());
    }

    private function handleEntity(object $entity): void
    {
        if (!method_exists($entity, 'setSlug') || !method_exists($entity, 'getSlugSource') || !method_exists($entity, 'getSlug')) {
            return;
        }

        $base = (string) $this->slugger->slug($entity->getSlugSource())->lower();

        if ($entity instanceof GroomerProfile && method_exists($entity, 'getCity') && null !== $entity->getCity() && $this->slugExists($entity, $base)) {
            $base = $base.'-'.$entity->getCity()->getSlug();
        }

        $slug = $base;
        $i = 2;
        while ($this->slugExists($entity, $slug)) {
            $slug = $base.'-'.$i;
            ++$i;
        }

        $entity->setSlug($slug);
    }

    private function slugExists(object $entity, string $slug): bool
    {
        $repository = $this->em->getRepository($entity::class);
        $existing = $repository->findOneBy(['slug' => $slug]);
        if (null === $existing) {
            return false;
        }

        return $existing !== $entity && method_exists($existing, 'getId') && method_exists($entity, 'getId') && $existing->getId() !== $entity->getId();
    }
}
