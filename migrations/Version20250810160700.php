<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250810160700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add service area, phone, services offered, price range to groomer_profile';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('groomer_profile');
        $table->addColumn('service_area', Types::STRING, ['length' => 120, 'notnull' => false]);
        $table->addColumn('phone', Types::STRING, ['length' => 32, 'notnull' => false]);
        $table->addColumn('services_offered', Types::TEXT, ['notnull' => false]);
        $table->addColumn('price_range', Types::STRING, ['length' => 64, 'notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('groomer_profile');
        $table->dropColumn('service_area');
        $table->dropColumn('phone');
        $table->dropColumn('services_offered');
        $table->dropColumn('price_range');
    }
}
