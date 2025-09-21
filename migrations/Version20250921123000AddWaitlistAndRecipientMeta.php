<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250921123000AddWaitlistAndRecipientMeta extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add commitment/contact/release metadata to lead_recipient and introduce lead_waitlist_entry table with indexes.';
    }

    public function up(Schema $schema): void
    {
        // LeadRecipient extra columns
        $this->addSql("ALTER TABLE lead_recipient ADD commitment_confirmed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("ALTER TABLE lead_recipient ADD contacted_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("ALTER TABLE lead_recipient ADD auto_released_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("ALTER TABLE lead_recipient ADD release_reason VARCHAR(255) DEFAULT NULL");
        $this->addSql('CREATE INDEX idx_lead_recipient_commitment_confirmed_at ON lead_recipient (commitment_confirmed_at)');
        $this->addSql('CREATE INDEX idx_lead_recipient_contacted_at ON lead_recipient (contacted_at)');
        $this->addSql('CREATE INDEX idx_lead_recipient_auto_released_at ON lead_recipient (auto_released_at)');

        // LeadWaitlistEntry table
        $this->addSql("CREATE TABLE lead_waitlist_entry (id INT AUTO_INCREMENT NOT NULL, lead_id INT NOT NULL, lead_recipient_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX idx_waitlist_lead (lead_id), INDEX idx_waitlist_recipient (lead_recipient_id), INDEX idx_waitlist_created_at (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE lead_waitlist_entry ADD CONSTRAINT FK_waitlist_lead FOREIGN KEY (lead_id) REFERENCES lead_capture (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lead_waitlist_entry ADD CONSTRAINT FK_waitlist_recipient FOREIGN KEY (lead_recipient_id) REFERENCES lead_recipient (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // LeadWaitlistEntry table
        $this->addSql('ALTER TABLE lead_waitlist_entry DROP FOREIGN KEY FK_waitlist_lead');
        $this->addSql('ALTER TABLE lead_waitlist_entry DROP FOREIGN KEY FK_waitlist_recipient');
        $this->addSql('DROP TABLE lead_waitlist_entry');

        // LeadRecipient extra columns
        $this->addSql('DROP INDEX idx_lead_recipient_commitment_confirmed_at ON lead_recipient');
        $this->addSql('DROP INDEX idx_lead_recipient_contacted_at ON lead_recipient');
        $this->addSql('DROP INDEX idx_lead_recipient_auto_released_at ON lead_recipient');
        $this->addSql('ALTER TABLE lead_recipient DROP commitment_confirmed_at');
        $this->addSql('ALTER TABLE lead_recipient DROP contacted_at');
        $this->addSql('ALTER TABLE lead_recipient DROP auto_released_at');
        $this->addSql('ALTER TABLE lead_recipient DROP release_reason');
    }
}

