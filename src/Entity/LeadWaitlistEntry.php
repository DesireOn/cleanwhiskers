<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\LeadWaitlistEntryRepository;

#[ORM\Entity(repositoryClass: LeadWaitlistEntryRepository::class)]
#[ORM\Table(name: 'lead_waitlist_entry')]
class LeadWaitlistEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Lead::class)]
    #[ORM\JoinColumn(name: 'lead_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Lead $lead;

    #[ORM\ManyToOne(targetEntity: LeadRecipient::class)]
    #[ORM\JoinColumn(name: 'lead_recipient_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?LeadRecipient $leadRecipient = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(Lead $lead, ?LeadRecipient $leadRecipient = null)
    {
        $this->lead = $lead;
        $this->leadRecipient = $leadRecipient;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getLead(): Lead { return $this->lead; }
    public function getLeadRecipient(): ?LeadRecipient { return $this->leadRecipient; }
    public function setLeadRecipient(?LeadRecipient $recipient): void { $this->leadRecipient = $recipient; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}

