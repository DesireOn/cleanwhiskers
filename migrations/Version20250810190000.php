<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250810190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create lead_capture table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('lead_capture');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('name', Types::STRING, ['length' => 255]);
        $table->addColumn('email', Types::STRING, ['length' => 255]);
        $table->addColumn('dog_breed', Types::STRING, ['length' => 255, 'notnull' => false]);
        $table->addColumn('city_id', Types::INTEGER, ['notnull' => true]);
        $table->addColumn('service_id', Types::INTEGER, ['notnull' => true]);
        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE, ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('city', ['city_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('service', ['service_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('lead_capture');
    }
}
