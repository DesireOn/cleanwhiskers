<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<City>
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    public function findOneBySlug(string $slug): ?City
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function existsBySlug(string $slug): bool
    {
        return null !== $this->createQueryBuilder('c')
            ->select('1')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return City[]
     */
    public function findTop(int $limit = 6): array
    {
        /** @var City[] $cities */
        $cities = $this->createQueryBuilder('c')
            ->select('partial c.{id, name, slug, seoIntro}')
            ->orderBy('c.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $cities;
    }

    /**
     * @return City[]
     */
    public function findByService(Service $service): array
    {
        /** @var City[] $cities */
        $cities = $this->createQueryBuilder('c')
            ->distinct()
            ->innerJoin(GroomerProfile::class, 'gp', 'WITH', 'gp.city = c')
            ->innerJoin('gp.services', 's')
            ->where('s = :service')
            ->setParameter('service', $service)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $cities;
    }
}
