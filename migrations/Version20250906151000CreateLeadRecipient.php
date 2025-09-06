<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250906151000CreateLeadRecipient extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create lead_recipient table with unique recipient_token and indexes';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('lead_recipient')) {
            return; // idempotent
        }

        $table = $schema->createTable('lead_recipient');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('lead_id', Types::INTEGER);
        $table->addColumn('groomer_id', Types::INTEGER);
        $table->addColumn('phone', Types::STRING, ['length' => 32]);
        $table->addColumn('recipient_token', Types::STRING, ['length' => 64]);
        $table->addColumn('notified_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $table->addColumn('clicked_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $table->addColumn('notification_status', Types::STRING, ['length' => 20, 'default' => 'queued']);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['recipient_token'], 'uniq_lead_recipient_token');
        $table->addIndex(['lead_id'], 'idx_lead_recipient_lead');
        $table->addIndex(['groomer_id'], 'idx_lead_recipient_groomer');

        $table->addForeignKeyConstraint('lead', ['lead_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('groomer_profile', ['groomer_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('lead_recipient')) {
            $schema->dropTable('lead_recipient');
        }
    }
}

