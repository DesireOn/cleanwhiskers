<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250810190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image_path to groomer_profile';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable('groomer_profile')->addColumn('image_path', Types::STRING, [
            'length' => 255,
            'notnull' => false,
        ]);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('groomer_profile')->dropColumn('image_path');
    }
}
