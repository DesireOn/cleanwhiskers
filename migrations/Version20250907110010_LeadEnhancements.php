<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250907110010_LeadEnhancements extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enhance lead_capture with additional columns and relations';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            $this->skipIf(true, sprintf('Skipping MySQL-specific migration on %s', $platform));
            return;
        }

        // Guard if table exists
        if (!$schema->hasTable('lead_capture')) {
            $this->skipIf(true, 'lead_capture table does not exist');
            return;
        }

        // Add missing columns if not present
        $columns = $this->connection->fetchFirstColumn("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lead_capture'");
        $have = array_map('strtolower', $columns);

        if (!in_array('phone', $have, true)) {
            $this->addSql("ALTER TABLE lead_capture ADD phone VARCHAR(32) DEFAULT NULL");
        }
        if (!in_array('pet_type', $have, true)) {
            $this->addSql("ALTER TABLE lead_capture ADD pet_type VARCHAR(50) DEFAULT NULL");
        }
        if (!in_array('consent_to_share', $have, true)) {
            $this->addSql("ALTER TABLE lead_capture ADD consent_to_share TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (!in_array('status', $have, true)) {
            $this->addSql("ALTER TABLE lead_capture ADD status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        }
        if (!in_array('claimed_by_id', $have, true)) {
            $this->addSql("ALTER TABLE lead_capture ADD claimed_by_id INT DEFAULT NULL");
        }
        if (!in_array('claimed_at', $have, true)) {
            $this->addSql("ALTER TABLE lead_capture ADD claimed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'");
        }
        if (!in_array('owner_token_hash', $have, true)) {
            // Add nullable first, backfill, then enforce NOT NULL
            $this->addSql("ALTER TABLE lead_capture ADD owner_token_hash VARCHAR(255) DEFAULT NULL");
        }
        // Backfill any NULL/empty hashes deterministically, then enforce NOT NULL
        $this->addSql("UPDATE lead_capture SET owner_token_hash = COALESCE(owner_token_hash, SHA1(CONCAT('init-', id, '-', email))) WHERE owner_token_hash IS NULL OR owner_token_hash = ''");
        // Make column NOT NULL if currently nullable
        $isNullable = (string) $this->connection->fetchOne(
            "SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lead_capture' AND COLUMN_NAME = 'owner_token_hash' LIMIT 1"
        );
        if (strtoupper($isNullable) === 'YES') {
            $this->addSql("ALTER TABLE lead_capture MODIFY owner_token_hash VARCHAR(255) NOT NULL");
        }
        if (!in_array('owner_token_expires_at', $have, true)) {
            $this->addSql("ALTER TABLE lead_capture ADD owner_token_expires_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'");
        }
        if (!in_array('updated_at', $have, true)) {
            $this->addSql("ALTER TABLE lead_capture ADD updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)' ");
            // Initialize updated_at to created_at where possible
            $this->addSql("UPDATE lead_capture SET updated_at = COALESCE(created_at, NOW()) WHERE updated_at IS NULL");
        }

        // Add FK for claimed_by_id if not existing
        $idxExists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lead_capture' AND INDEX_NAME = 'IDX_LEAD_CAPTURE_CLAIMED_BY'"
        );
        if ($idxExists === 0) {
            $this->addSql('CREATE INDEX IDX_LEAD_CAPTURE_CLAIMED_BY ON lead_capture (claimed_by_id)');
        }
        $fkExists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'FK_LEAD_CAPTURE_CLAIMED_BY'"
        );
        if ($fkExists === 0) {
            $this->addSql('ALTER TABLE lead_capture ADD CONSTRAINT FK_LEAD_CAPTURE_CLAIMED_BY FOREIGN KEY (claimed_by_id) REFERENCES groomer_profile (id)');
        }
    }
}
