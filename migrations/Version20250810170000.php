<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250810170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add seo_intro column to city table';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable('city')->addColumn('seo_intro', Types::TEXT, ['notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('city')->dropColumn('seo_intro');
    }
}
