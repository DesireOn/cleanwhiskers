<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250830120000CreateSeoContent extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create seo_content table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('seo_content');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('city_id', Types::INTEGER);
        $table->addColumn('service_id', Types::INTEGER);
        $table->addColumn('title', Types::STRING, ['length' => 255]);
        $table->addColumn('content', Types::TEXT);
        $table->addColumn('image_path', Types::STRING, ['length' => 255, 'notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['city_id', 'service_id'], 'uniq_seo_city_service');
        $table->addForeignKeyConstraint('city', ['city_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('service', ['service_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('seo_content');
    }
}
