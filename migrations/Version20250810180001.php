<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250810180001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add verified flag to review table';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable('review')->addColumn('verified', Types::BOOLEAN, [
            'default' => false,
        ]);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('review')->dropColumn('verified');
    }
}
