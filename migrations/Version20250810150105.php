<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250810150105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create groomer_profile_service join table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('groomer_profile_service');
        $table->addColumn('groomer_profile_id', Types::INTEGER);
        $table->addColumn('service_id', Types::INTEGER);
        $table->setPrimaryKey(['groomer_profile_id', 'service_id']);
        $table->addIndex(['groomer_profile_id'], 'IDX_GROOMER_PROFILE_SERVICE_GROOMER_PROFILE_ID');
        $table->addIndex(['service_id'], 'IDX_GROOMER_PROFILE_SERVICE_SERVICE_ID');
        $table->addForeignKeyConstraint('groomer_profile', ['groomer_profile_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('service', ['service_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('groomer_profile_service');
    }
}
