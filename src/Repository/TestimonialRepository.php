<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\Testimonial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Testimonial>
 */
class TestimonialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Testimonial::class);
    }

    /**
     * @return list<Testimonial>
     */
    public function findAllOrderedByDate(): array
    {
        /** @var list<Testimonial> $result */
        $result = $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return array{excerpt: string, is_placeholder: bool}|null
     */
    public function findOneForGroomer(GroomerProfile $groomer): ?array
    {
        $review = $this->getEntityManager()->getRepository(Review::class)
            ->findOneBy(['groomer' => $groomer], ['createdAt' => 'DESC']);

        if ($review instanceof Review) {
            return [
                'excerpt' => $this->excerpt($review->getComment()),
                'is_placeholder' => false,
            ];
        }

        $testimonial = $this->createQueryBuilder('t')
            ->where('t.isPlaceholder = :placeholder')
            ->setParameter('placeholder', true)
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($testimonial instanceof Testimonial) {
            return [
                'excerpt' => $this->excerpt($testimonial->getQuote()),
                'is_placeholder' => true,
            ];
        }

        return null;
    }

    private function excerpt(string $text, int $max = 120): string
    {
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max - 3).'...' : $text;
    }
}
