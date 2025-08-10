<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class GroomerProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroomerProfile::class);
    }

    public function paginatedByCityAndService(City $city, Service $service, int $page, string $sort): Paginator
    {
        $qb = $this->createQueryBuilder('g')
            ->innerJoin('g.services', 'gs')
            ->andWhere('g.primaryCity = :city')->setParameter('city', $city)
            ->andWhere('gs.service = :service')->setParameter('service', $service)
            ->andWhere('g.isVerified = true');

        $order = match ($sort) {
            'name'    => ['g.name' => 'ASC'],
            'reviews' => ['g.ratingCount' => 'DESC', 'g.ratingAvg' => 'DESC'],
            default   => ['g.ratingAvg' => 'DESC', 'g.ratingCount' => 'DESC'],
        };
        foreach ($order as $field => $direction) {
            $qb->addOrderBy($field, $direction);
        }

        return $this->paginate($qb, $page, 20);
    }

    private function paginate(QueryBuilder $qb, int $page, int $limit): Paginator
    {
        $query = $qb->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }
}
