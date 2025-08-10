<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name: 'groomer_service', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_groomer_service', columns: ['groomer_id', 'service_id'])
], indexes: [
    new ORM\Index(name: 'idx_service_groomer', columns: ['service_id', 'groomer_id'])
])]
class GroomerService
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GroomerProfile::class, inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: false)]
    private GroomerProfile $groomer;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Service $service;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $priceFrom = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $durationMin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroomer(): GroomerProfile
    {
        return $this->groomer;
    }

    public function setGroomer(GroomerProfile $groomer): self
    {
        $this->groomer = $groomer;
        return $this;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function setService(Service $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function getPriceFrom(): ?int
    {
        return $this->priceFrom;
    }

    public function setPriceFrom(?int $priceFrom): self
    {
        $this->priceFrom = $priceFrom;
        return $this;
    }

    public function getDurationMin(): ?int
    {
        return $this->durationMin;
    }

    public function setDurationMin(?int $durationMin): self
    {
        $this->durationMin = $durationMin;
        return $this;
    }
}
