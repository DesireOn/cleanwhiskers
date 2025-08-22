<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250822104000AddCityCoverageNotes extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add coverage_notes column to city';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('city');
        $table->addColumn('coverage_notes', Types::TEXT, ['notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('city')->dropColumn('coverage_notes');
    }
}
