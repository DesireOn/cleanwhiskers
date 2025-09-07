<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250830151030AddNumericPrice extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add nullable numeric price to groomer_profile with index for sorting';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('groomer_profile')) {
            return;
        }

        $table = $schema->getTable('groomer_profile');
        if (!$table->hasColumn('price')) {
            $table->addColumn('price', Types::INTEGER, ['notnull' => false]);
        }
        if (!$table->hasIndex('idx_groomer_profile_price')) {
            $table->addIndex(['price'], 'idx_groomer_profile_price');
        }
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('groomer_profile')) {
            return;
        }
        $table = $schema->getTable('groomer_profile');
        if ($table->hasIndex('idx_groomer_profile_price')) {
            $table->dropIndex('idx_groomer_profile_price');
        }
        if ($table->hasColumn('price')) {
            $table->dropColumn('price');
        }
    }
}

