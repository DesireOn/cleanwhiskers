<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250810160647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create booking_request table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('booking_request');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('groomer_id', Types::INTEGER);
        $table->addColumn('pet_owner_id', Types::INTEGER);
        $table->addColumn('service_id', Types::INTEGER, ['notnull' => false]);
        $table->addColumn('status', Types::STRING, ['length' => 20]);
        $table->addColumn('requested_at', Types::DATETIME_IMMUTABLE);
        $table->addColumn('notes', Types::TEXT, ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['service_id'], 'idx_booking_request_service');
        $table->addIndex(['groomer_id'], 'idx_booking_request_groomer');
        $table->addIndex(['pet_owner_id'], 'idx_booking_request_pet_owner');
        $table->addIndex(['status'], 'idx_booking_request_status');
        $table->addForeignKeyConstraint('groomer_profile', ['groomer_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('`user`', ['pet_owner_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('service', ['service_id'], ['id'], ['onDelete' => 'SET NULL']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('booking_request');
    }
}
