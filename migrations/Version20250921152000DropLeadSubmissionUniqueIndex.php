<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250921152000DropLeadSubmissionUniqueIndex extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop unique index on lead_capture.submission_fingerprint to allow time-window dedupe instead.';
    }

    public function up(Schema $schema): void
    {
        // Drop index if it exists
        // MySQL: index name must match previous migration
        $this->addSql('DROP INDEX uniq_lead_submission_fingerprint ON lead_capture');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("CREATE UNIQUE INDEX uniq_lead_submission_fingerprint ON lead_capture (submission_fingerprint)");
    }
}

