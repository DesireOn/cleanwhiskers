<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\LeadRecipientRepository;

#[ORM\Entity(repositoryClass: LeadRecipientRepository::class)]
#[ORM\Table(name: 'lead_recipient')]
#[ORM\UniqueConstraint(name: 'uniq_lead_recipient_lead_email', columns: ['lead_id', 'email'])]
class LeadRecipient
{
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENT = 'sent';
    public const STATUS_BOUNCED = 'bounced';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Lead::class)]
    #[ORM\JoinColumn(name: 'lead_id', referencedColumnName: 'id', nullable: false)]
    private Lead $lead;

    #[ORM\ManyToOne(targetEntity: GroomerProfile::class)]
    #[ORM\JoinColumn(name: 'groomer_profile_id', referencedColumnName: 'id', nullable: true)]
    private ?GroomerProfile $groomerProfile = null;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(length: 20, options: ['default' => self::STATUS_QUEUED])]
    private string $status = self::STATUS_QUEUED;

    #[ORM\Column(name: 'invite_sent_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $inviteSentAt = null;

    #[ORM\Column(name: 'claim_token_hash', length: 255)]
    private string $claimTokenHash;

    #[ORM\Column(name: 'token_expires_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $tokenExpiresAt;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'claimed_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $claimedAt = null;

    public function __construct(Lead $lead, string $email, string $claimTokenHash, \DateTimeImmutable $tokenExpiresAt)
    {
        $this->lead = $lead;
        $this->email = $email;
        $this->claimTokenHash = $claimTokenHash;
        $this->tokenExpiresAt = $tokenExpiresAt;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getLead(): Lead { return $this->lead; }

    public function getGroomerProfile(): ?GroomerProfile { return $this->groomerProfile; }
    public function setGroomerProfile(?GroomerProfile $groomerProfile): void { $this->groomerProfile = $groomerProfile; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function getInviteSentAt(): ?\DateTimeImmutable { return $this->inviteSentAt; }
    public function setInviteSentAt(?\DateTimeImmutable $inviteSentAt): void { $this->inviteSentAt = $inviteSentAt; }

    public function getClaimTokenHash(): string { return $this->claimTokenHash; }
    public function setClaimTokenHash(string $hash): void { $this->claimTokenHash = $hash; }

    public function getTokenExpiresAt(): \DateTimeImmutable { return $this->tokenExpiresAt; }
    public function setTokenExpiresAt(\DateTimeImmutable $at): void { $this->tokenExpiresAt = $at; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getClaimedAt(): ?\DateTimeImmutable { return $this->claimedAt; }
    public function setClaimedAt(?\DateTimeImmutable $claimedAt): void { $this->claimedAt = $claimedAt; }
}
