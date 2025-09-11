<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250911195000AddLeadRecipientClaimedAt extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add claimed_at to lead_recipient to allow repeat viewing by the claimer.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE lead_recipient ADD claimed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql('CREATE INDEX idx_lead_recipient_claimed_at ON lead_recipient (claimed_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_lead_recipient_claimed_at ON lead_recipient');
        $this->addSql('ALTER TABLE lead_recipient DROP claimed_at');
    }
}

