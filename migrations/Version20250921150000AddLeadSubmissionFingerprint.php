<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250921150000AddLeadSubmissionFingerprint extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add submission_fingerprint to lead_capture with unique index to deduplicate identical submissions.';
    }

    public function up(Schema $schema): void
    {
        // nullable to avoid backfill; unique index enforces idempotency for new rows
        $this->addSql("ALTER TABLE lead_capture ADD submission_fingerprint VARCHAR(64) DEFAULT NULL");
        $this->addSql("CREATE UNIQUE INDEX uniq_lead_submission_fingerprint ON lead_capture (submission_fingerprint)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_lead_submission_fingerprint ON lead_capture');
        $this->addSql('ALTER TABLE lead_capture DROP submission_fingerprint');
    }
}

