<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250822113001AddMobileDogGroomingService extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert Mobile Dog Grooming service if not exists';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO service (name, slug) SELECT 'Mobile Dog Grooming', 'mobile-dog-grooming' WHERE NOT EXISTS (SELECT 1 FROM service WHERE slug = 'mobile-dog-grooming')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM service WHERE slug = 'mobile-dog-grooming'");
    }
}
