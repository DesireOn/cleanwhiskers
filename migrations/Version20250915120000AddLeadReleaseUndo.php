<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250915120000AddLeadReleaseUndo extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add release_allowed_until and released_at to lead_recipient to support time-limited undo.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE lead_recipient ADD release_allowed_until DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("ALTER TABLE lead_recipient ADD released_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lead_recipient DROP release_allowed_until');
        $this->addSql('ALTER TABLE lead_recipient DROP released_at');
    }
}

