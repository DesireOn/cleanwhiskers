<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250810150100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create groomer_profile table with foreign keys to user and city';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('groomer_profile');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('user_id', Types::INTEGER);
        $table->addColumn('city_id', Types::INTEGER);
        $table->addColumn('business_name', Types::STRING, ['length' => 255]);
        $table->addColumn('slug', Types::STRING, ['length' => 255]);
        $table->addColumn('about', Types::TEXT);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['slug'], 'UNIQ_GROOMER_PROFILE_SLUG');
        $table->addIndex(['slug'], 'idx_groomer_profile_slug');
        $table->addForeignKeyConstraint('`user`', ['user_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('city', ['city_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('groomer_profile');
    }
}
