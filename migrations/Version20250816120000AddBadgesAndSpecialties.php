<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250816120000AddBadgesAndSpecialties extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add badges and specialties to groomer_profile';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('groomer_profile');
        $table->addColumn('badges', Types::JSON, ['notnull' => false]);
        $table->addColumn('specialties', Types::JSON, ['notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('groomer_profile');
        $table->dropColumn('badges');
        $table->dropColumn('specialties');
    }
}
