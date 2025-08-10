<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Service;
use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use App\Repository\ServiceRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Query;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class SlugListener
{
    public function __construct(
        private CityRepository $cityRepository,
        private ServiceRepository $serviceRepository,
        private GroomerProfileRepository $groomerProfileRepository,
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->handleEvent($args->getObject());
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->handleEvent($args->getObject());
    }

    private function handleEvent(object $entity): void
    {
        if ($entity instanceof City) {
            $this->ensureSlug($entity, $this->cityRepository, $entity->getName());
        } elseif ($entity instanceof Service) {
            $this->ensureSlug($entity, $this->serviceRepository, $entity->getName());
        } elseif ($entity instanceof GroomerProfile) {
            $this->ensureSlug($entity, $this->groomerProfileRepository, $entity->getBusinessName());
        }
    }

    private function ensureSlug(
        object $entity,
        CityRepository|ServiceRepository|GroomerProfileRepository $repository,
        string $source,
    ): void {
        if (method_exists($entity, 'getSlug') && method_exists($entity, 'refreshSlugFrom')) {
            /** @var string $slug */
            $slug = $entity->getSlug();
            if ('' === $slug) {
                $entity->refreshSlugFrom($source);
                /** @var string $slug */
                $slug = $entity->getSlug();
            }

            $existing = $repository->createQueryBuilder('e')
                ->where('e.slug = :slug')
                ->setParameter('slug', $slug)
                ->getQuery()
                ->setMaxResults(1)
                ->setHint(Query::HINT_REFRESH, true)
                ->getOneOrNullResult();

            if (null !== $existing && $existing !== $entity) {
                throw new \InvalidArgumentException('Slug already exists.');
            }
        }
    }
}
