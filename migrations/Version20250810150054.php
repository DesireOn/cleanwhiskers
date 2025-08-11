<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250810150054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create service table with unique slug';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('service');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('name', Types::STRING, ['length' => 255]);
        $table->addColumn('slug', Types::STRING, ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['slug'], 'UNIQ_E19D9AD2989D9B62');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('service');
    }
}
