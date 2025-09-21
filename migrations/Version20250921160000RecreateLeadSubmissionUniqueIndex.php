<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250921160000RecreateLeadSubmissionUniqueIndex extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Recreate unique index on lead_capture.submission_fingerprint for window-bucketed dedupe.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE UNIQUE INDEX uniq_lead_submission_fingerprint ON lead_capture (submission_fingerprint)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_lead_submission_fingerprint ON lead_capture');
    }
}

