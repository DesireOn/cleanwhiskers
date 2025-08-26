<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\City;
use App\Repository\CityRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CityListExtension extends AbstractExtension
{
    public function __construct(private CityRepository $cityRepository)
    {
    }

    /**
     * @return City[]
     */
    public function getCityList(): array
    {
        return $this->cityRepository->findBy([], ['name' => 'ASC']);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('city_list', [$this, 'getCityList']),
        ];
    }
}
