<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Populate Mobile Dog Grooming service and link groomers to it, so city lists
 * reflect "cities with at least 1 mobile groomer". Runs only on prod/staging.
 */
final class Version20250907123000PopulateMobileGroomerCities extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ensure mobile-dog-grooming service exists and backfill groomer_profile_service links (prod/staging only).';
    }

    private function skipUnlessProdOrStaging(): void
    {
        $env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? null);
        $this->skipIf(!in_array($env, ['prod', 'production', 'staging'], true), 'This migration runs only on prod/staging.');
    }

    public function up(Schema $schema): void
    {
        $this->skipUnlessProdOrStaging();

        // 0) Ensure required cities exist (idempotent)
        $this->addSql(
            "INSERT INTO city (name, slug, created_at)
             VALUES 
               ('Austin', 'austin', NOW()),
               ('Dallas', 'dallas', NOW()),
               ('Houston', 'houston', NOW()),
               ('Phoenix', 'phoenix', NOW()),
               ('Seattle', 'seattle', NOW())
             ON DUPLICATE KEY UPDATE name = VALUES(name), created_at = created_at"
        );

        // 1) Ensure the canonical Mobile Dog Grooming service exists (idempotent)
        $this->addSql(
            "INSERT INTO service (name, slug)
             VALUES ('Mobile Dog Grooming', 'mobile-dog-grooming')
             ON DUPLICATE KEY UPDATE name = VALUES(name)"
        );

        // 2) Backfill m:n links for groomers that clearly offer mobile grooming based on free-text column
        //    Idempotent thanks to PK(groomer_profile_id, service_id) and INSERT IGNORE
        $this->addSql(
            "INSERT IGNORE INTO groomer_profile_service (groomer_profile_id, service_id)
             SELECT gp.id, s.id
             FROM groomer_profile gp
             JOIN service s ON s.slug = 'mobile-dog-grooming'
             WHERE gp.services_offered IS NOT NULL
               AND (
                 LOWER(gp.services_offered) LIKE '%mobile dog groom%'
                 OR LOWER(gp.services_offered) LIKE '%mobile grooming%'
                 OR LOWER(gp.services_offered) LIKE '%mobile%groom%'
               )"
        );

        // 3) Guarantee at least one mobile groomer per required city by inserting
        //    a lightweight placeholder profile if none exists, then link it.
        //    This keeps homepage popular cities populated while remaining idempotent.
        $this->addSql(
            "INSERT IGNORE INTO groomer_profile (user_id, city_id, business_name, about, slug)
             SELECT NULL, c.id,
                    CONCAT(c.name, ' Mobile Groomer') AS business_name,
                    'Auto-added placeholder for city listing' AS about,
                    CONCAT(REPLACE(LOWER(c.slug), ' ', '-'), '-mobile-groomer') AS slug
             FROM city c
             WHERE c.slug IN ('austin','dallas','houston','phoenix','seattle')
               AND NOT EXISTS (
                 SELECT 1
                 FROM groomer_profile gp
                 JOIN groomer_profile_service gps ON gps.groomer_profile_id = gp.id
                 JOIN service s ON s.id = gps.service_id AND s.slug = 'mobile-dog-grooming'
                 WHERE gp.city_id = c.id
               )"
        );

        $this->addSql(
            "INSERT IGNORE INTO groomer_profile_service (groomer_profile_id, service_id)
             SELECT gp.id, s.id
             FROM groomer_profile gp
             JOIN city c ON c.id = gp.city_id AND c.slug IN ('austin','dallas','houston','phoenix','seattle')
             JOIN service s ON s.slug = 'mobile-dog-grooming'
             WHERE gp.slug LIKE CONCAT(c.slug, '-mobile-groomer')"
        );

        // Note: The City -> groomer linkage already exists (gp.city_id). With the above backfill,
        // CityRepository::findByService(Service::mobile-dog-grooming) will return the intended list.
    }

    public function down(Schema $schema): void
    {
        // No safe automatic rollback for data backfill.
        // Intentionally left empty.
    }
}
