<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250830162000DropPriceRange extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop deprecated price_range column from groomer_profile';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('groomer_profile')) {
            return;
        }

        $table = $schema->getTable('groomer_profile');
        if ($table->hasColumn('price_range')) {
            $table->dropColumn('price_range');
        }
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('groomer_profile')) {
            return;
        }

        $table = $schema->getTable('groomer_profile');
        if (!$table->hasColumn('price_range')) {
            // Recreate as nullable string to allow rollback
            $table->addColumn('price_range', 'string', [
                'length' => 64,
                'notnull' => false,
            ]);
        }
    }
}

