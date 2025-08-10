<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use App\Domain\Shared\Exception\SlugCollisionException;
use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Service;
use App\Repository\CityRepository;
use App\Repository\GroomerProfileRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class SlugListener
{
    public function __construct(
        private readonly CityRepository $cityRepository,
        private readonly ServiceRepository $serviceRepository,
        private readonly GroomerProfileRepository $groomerProfileRepository,
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof City) {
            if ('' === $entity->getSlug()) {
                $entity->refreshSlugFrom($entity->getName());
            }
            $this->ensureCitySlugUnique($entity);
        } elseif ($entity instanceof Service) {
            if ('' === $entity->getSlug()) {
                $entity->refreshSlugFrom($entity->getName());
            }
            $this->ensureServiceSlugUnique($entity);
        } elseif ($entity instanceof GroomerProfile) {
            if ('' === $entity->getSlug()) {
                $entity->refreshSlugFrom($entity->getBusinessName());
            }
            $this->ensureGroomerProfileSlugUnique($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $em = $args->getObjectManager();

        if ($entity instanceof City) {
            $this->handleCityUpdate($entity, $args, $em);
        } elseif ($entity instanceof Service) {
            $this->handleServiceUpdate($entity, $args, $em);
        } elseif ($entity instanceof GroomerProfile) {
            $this->handleGroomerProfileUpdate($entity, $args, $em);
        }
    }

    private function handleCityUpdate(City $city, PreUpdateEventArgs $args, EntityManagerInterface $em): void
    {
        $oldSlug = $city->getSlug();
        if ($args->hasChangedField('name')) {
            $city->refreshSlugFrom($city->getName());
            $this->recompute($em, $city, 'slug', $oldSlug, $city->getSlug());
        }

        if ($oldSlug !== $city->getSlug() || $args->hasChangedField('slug')) {
            $this->ensureCitySlugUnique($city);
        }
    }

    private function handleServiceUpdate(Service $service, PreUpdateEventArgs $args, EntityManagerInterface $em): void
    {
        $oldSlug = $service->getSlug();
        if ($args->hasChangedField('name')) {
            $service->refreshSlugFrom($service->getName());
            $this->recompute($em, $service, 'slug', $oldSlug, $service->getSlug());
        }

        if ($oldSlug !== $service->getSlug() || $args->hasChangedField('slug')) {
            $this->ensureServiceSlugUnique($service);
        }
    }

    private function handleGroomerProfileUpdate(GroomerProfile $profile, PreUpdateEventArgs $args, EntityManagerInterface $em): void
    {
        $oldSlug = $profile->getSlug();
        if ($args->hasChangedField('businessName')) {
            $profile->refreshSlugFrom($profile->getBusinessName());
            $this->recompute($em, $profile, 'slug', $oldSlug, $profile->getSlug());
        }

        if ($oldSlug !== $profile->getSlug() || $args->hasChangedField('slug')) {
            $this->ensureGroomerProfileSlugUnique($profile);
        }
    }

    private function recompute(EntityManagerInterface $em, object $entity, string $field, mixed $oldValue, mixed $newValue): void
    {
        $uow = $em->getUnitOfWork();
        $uow->propertyChanged($entity, $field, $oldValue, $newValue);
        $uow->recomputeSingleEntityChangeSet($em->getClassMetadata($entity::class), $entity);
    }

    private function ensureCitySlugUnique(City $city): void
    {
        if ($this->cityRepository->existsBySlug($city->getSlug())) {
            throw new SlugCollisionException('City slug already exists.');
        }
    }

    private function ensureServiceSlugUnique(Service $service): void
    {
        if ($this->serviceRepository->existsBySlug($service->getSlug())) {
            throw new SlugCollisionException('Service slug already exists.');
        }
    }

    private function ensureGroomerProfileSlugUnique(GroomerProfile $profile): void
    {
        if ($this->groomerProfileRepository->existsBySlug($profile->getSlug())) {
            throw new SlugCollisionException('GroomerProfile slug already exists.');
        }
    }
}
