<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\City;
use App\Repository\CityRepository;
use App\Repository\ServiceRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CityListExtension extends AbstractExtension
{
    public function __construct(
        private CityRepository $cityRepository,
        private ServiceRepository $serviceRepository,
    ) {
    }

    /**
     * @return City[]
     */
    public function getCityList(): array
    {
        $service = $this->serviceRepository->findMobileDogGroomingService();
        if (null === $service) {
            return [];
        }

        return $this->cityRepository->findByService($service);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('city_list', [$this, 'getCityList']),
        ];
    }
}
