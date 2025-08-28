<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250823120000AddIsPlaceholderToTestimonial extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_placeholder flag to testimonial';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable('testimonial')->addColumn('is_placeholder', Types::BOOLEAN, [
            'default' => false,
        ]);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('testimonial')->dropColumn('is_placeholder');
    }
}
