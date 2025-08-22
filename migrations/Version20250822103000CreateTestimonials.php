<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250822103000CreateTestimonials extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create testimonial table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('testimonial');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('name', Types::STRING, ['length' => 255]);
        $table->addColumn('city', Types::STRING, ['length' => 255]);
        $table->addColumn('quote', Types::TEXT);
        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['created_at'], 'idx_testimonial_created_at');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('testimonial');
    }
}
