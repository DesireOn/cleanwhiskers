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
        $schema->getTable('review')->addIndex(['rating'], 'idx_review_rating');
        $schema->getTable('groomer_profile')->addIndex(['city_id'], 'idx_groomer_profile_city');
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('review')->dropIndex('idx_review_rating');
        $schema->getTable('groomer_profile')->dropIndex('idx_groomer_profile_city');
    }
}
