<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LeadRecipientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeadRecipientRepository::class)]
#[ORM\Table(name: 'lead_recipient')]
#[ORM\Index(name: 'idx_lead_recipient_lead', columns: ['lead_id'])]
#[ORM\Index(name: 'idx_lead_recipient_groomer', columns: ['groomer_id'])]
class LeadRecipient
{
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Lead::class)]
    #[ORM\JoinColumn(name: 'lead_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Lead $lead;

    #[ORM\ManyToOne(targetEntity: GroomerProfile::class)]
    #[ORM\JoinColumn(name: 'groomer_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private GroomerProfile $groomer;

    #[ORM\Column(length: 32)]
    private string $phone;

    #[ORM\Column(name: 'recipient_token', length: 64, unique: true)]
    private string $recipientToken;

    #[ORM\Column(name: 'notified_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $notifiedAt = null;

    #[ORM\Column(name: 'clicked_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $clickedAt = null;

    #[ORM\Column(name: 'notification_status', length: 20)]
    private string $notificationStatus = self::STATUS_QUEUED;

    public function __construct(Lead $lead, GroomerProfile $groomer, string $phone, string $recipientToken)
    {
        $this->lead = $lead;
        $this->groomer = $groomer;
        $this->phone = $phone;
        $this->recipientToken = $recipientToken;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLead(): Lead
    {
        return $this->lead;
    }

    public function getGroomer(): GroomerProfile
    {
        return $this->groomer;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getRecipientToken(): string
    {
        return $this->recipientToken;
    }

    public function getNotifiedAt(): ?\DateTimeImmutable
    {
        return $this->notifiedAt;
    }

    public function setNotifiedAt(?\DateTimeImmutable $notifiedAt): self
    {
        $this->notifiedAt = $notifiedAt;
        return $this;
    }

    public function getClickedAt(): ?\DateTimeImmutable
    {
        return $this->clickedAt;
    }

    public function setClickedAt(?\DateTimeImmutable $clickedAt): self
    {
        $this->clickedAt = $clickedAt;
        return $this;
    }

    public function getNotificationStatus(): string
    {
        return $this->notificationStatus;
    }

    public function setNotificationStatus(string $status): self
    {
        $allowed = [self::STATUS_QUEUED, self::STATUS_SENT, self::STATUS_FAILED];
        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid notification status.');
        }
        $this->notificationStatus = $status;
        return $this;
    }
}

