<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250907110030_CreateLeadRecipient extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create lead_recipient table for outreach recipients';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('lead_recipient')) {
            return;
        }

        $table = $schema->createTable('lead_recipient');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('lead_id', Types::INTEGER);
        $table->addColumn('groomer_profile_id', Types::INTEGER, ['notnull' => false]);
        $table->addColumn('email', Types::STRING, ['length' => 255]);
        $table->addColumn('status', Types::STRING, ['length' => 20, 'default' => 'queued']);
        $table->addColumn('invite_sent_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $table->addColumn('claim_token_hash', Types::STRING, ['length' => 255]);
        $table->addColumn('token_expires_at', Types::DATETIME_IMMUTABLE);
        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['lead_id'], 'IDX_LEAD_RECIPIENT_LEAD');
        $table->addIndex(['groomer_profile_id'], 'IDX_LEAD_RECIPIENT_GROOMER');
        $table->addUniqueIndex(['lead_id', 'email'], 'uniq_lead_recipient_lead_email');

        // FK constraints will be added in SQL to ensure ON UPDATE/DELETE defaults
        $this->addSql('ALTER TABLE lead_recipient ADD CONSTRAINT FK_LEAD_RECIPIENT_LEAD FOREIGN KEY (lead_id) REFERENCES lead_capture (id)');
        $this->addSql('ALTER TABLE lead_recipient ADD CONSTRAINT FK_LEAD_RECIPIENT_GROOMER FOREIGN KEY (groomer_profile_id) REFERENCES groomer_profile (id)');
    }
}

