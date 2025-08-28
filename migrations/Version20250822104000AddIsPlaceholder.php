<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250822104000AddIsPlaceholder extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_placeholder flag to testimonial table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('testimonial');
        $table->addColumn('is_placeholder', Types::BOOLEAN, ['default' => false]);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('testimonial');
        $table->dropColumn('is_placeholder');
    }
}
