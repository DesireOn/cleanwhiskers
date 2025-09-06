<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250906150000CreateLead extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create lead table with indexes and unique claim_token';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('lead')) {
            return; // idempotent
        }

        $table = $schema->createTable('lead');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('city_id', Types::INTEGER);
        $table->addColumn('service_id', Types::INTEGER);
        $table->addColumn('pet_type', Types::STRING, ['length' => 32]);
        $table->addColumn('breed_size', Types::STRING, ['length' => 32, 'notnull' => false]);
        $table->addColumn('owner_name', Types::STRING, ['length' => 255]);
        $table->addColumn('owner_phone', Types::STRING, ['length' => 32]);
        $table->addColumn('owner_email', Types::STRING, ['length' => 255, 'notnull' => false]);
        $table->addColumn('status', Types::STRING, ['length' => 20]);
        $table->addColumn('claim_token', Types::STRING, ['length' => 64]);
        $table->addColumn('claimed_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $table->addColumn('claimed_by_id', Types::INTEGER, ['notnull' => false]);
        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $table->addColumn('claim_fee_cents', Types::INTEGER, ['default' => 0]);
        $table->addColumn('billing_status', Types::STRING, ['length' => 20, 'default' => 'unbilled']);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['claim_token'], 'uniq_lead_claim_token');
        $table->addIndex(['city_id'], 'idx_lead_city');
        $table->addIndex(['service_id'], 'idx_lead_service');
        $table->addIndex(['status'], 'idx_lead_status');

        $table->addForeignKeyConstraint('city', ['city_id'], ['id']);
        $table->addForeignKeyConstraint('service', ['service_id'], ['id']);
        $table->addForeignKeyConstraint('groomer_profile', ['claimed_by_id'], ['id'], ['onDelete' => 'SET NULL']);
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('lead')) {
            $schema->dropTable('lead');
        }
    }
}

