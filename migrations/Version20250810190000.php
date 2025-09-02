<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250810190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add indexes on review.rating and groomer_profile.city_id';
    }

    public function up(Schema $schema): void
    {
        // No-op: indexes are handled by later migrations and entity metadata.
        // Keeping this migration empty prevents duplicate index creation on fresh installs.
    }

    public function down(Schema $schema): void
    {
        // No-op to mirror up().
    }
}
