<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250810150110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create review table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('review');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('groomer_id', Types::INTEGER);
        $table->addColumn('author_id', Types::INTEGER);
        $table->addColumn('rating', Types::INTEGER);
        $table->addColumn('comment', Types::TEXT);
        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['groomer_id', 'created_at'], 'idx_review_groomer_created_at');
        $table->addForeignKeyConstraint('groomer_profile', ['groomer_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('`user`', ['author_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('review');
    }
}
