<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AuditLogRepository;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\Table(name: 'audit_log')]
#[ORM\Index(name: 'idx_audit_event', columns: ['event'])]
#[ORM\Index(name: 'idx_audit_subject', columns: ['subject_type', 'subject_id'])]
class AuditLog
{
    public const ACTOR_SYSTEM = 'system';
    public const ACTOR_GROOMER = 'groomer';
    public const ACTOR_OWNER = 'owner';

    public const SUBJECT_LEAD = 'lead';
    public const SUBJECT_GROOMER = 'groomer';
    public const SUBJECT_EMAIL = 'email';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private string $event;

    #[ORM\Column(name: 'actor_type', length: 16)]
    private string $actorType;

    #[ORM\Column(name: 'actor_id', type: Types::INTEGER, nullable: true)]
    private ?int $actorId = null;

    #[ORM\Column(name: 'subject_type', length: 16)]
    private string $subjectType;

    #[ORM\Column(name: 'subject_id', type: Types::INTEGER)]
    private int $subjectId;

    /** @var array<string,mixed> */
    #[ORM\Column(type: Types::JSON)]
    private array $metadata = [];

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    /**
     * @param array<string,mixed> $metadata
     */
    public function __construct(string $event, string $actorType, ?int $actorId, string $subjectType, int $subjectId, array $metadata = [])
    {
        $this->event = $event;
        $this->actorType = $actorType;
        $this->actorId = $actorId;
        $this->subjectType = $subjectType;
        $this->subjectId = $subjectId;
        $this->metadata = $metadata;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getEvent(): string { return $this->event; }
    public function getActorType(): string { return $this->actorType; }
    public function getActorId(): ?int { return $this->actorId; }
    public function getSubjectType(): string { return $this->subjectType; }
    public function getSubjectId(): int { return $this->subjectId; }
    /** @return array<string,mixed> */
    public function getMetadata(): array { return $this->metadata; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
