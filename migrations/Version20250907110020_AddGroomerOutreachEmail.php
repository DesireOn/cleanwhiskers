<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250907110020_AddGroomerOutreachEmail extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add outreach_email to groomer_profile';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            $this->skipIf(true, sprintf('Skipping MySQL-specific migration on %s', $platform));
            return;
        }

        $has = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'groomer_profile' AND COLUMN_NAME = 'outreach_email'"
        );
        if ($has === 0) {
            $this->addSql("ALTER TABLE groomer_profile ADD outreach_email VARCHAR(255) DEFAULT NULL");
        }
    }
}

