<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828131852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            $this->skipIf(true, sprintf('Skipping MySQL-specific migration on %s', $platform));
            return;
        }
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lead_capture (id INT AUTO_INCREMENT NOT NULL, city_id INT NOT NULL, service_id INT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, dog_breed VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D230FA8B8BAC62AF (city_id), INDEX IDX_D230FA8BED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lead_capture ADD CONSTRAINT FK_D230FA8B8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE lead_capture ADD CONSTRAINT FK_D230FA8BED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('CREATE INDEX idx_blog_post_slugs_slug ON blog_post_slugs (slug)');
        $this->addSql('ALTER TABLE blog_post_slugs RENAME INDEX uniq_blog_post_slugs_slug TO UNIQ_C9F7FCE2989D9B62');
        $this->addSql('ALTER TABLE blog_post_slugs RENAME INDEX idx_blog_post_slugs_post_id TO IDX_C9F7FCE24B89032C');
        $this->addSql('ALTER TABLE booking_request DROP FOREIGN KEY FK_6129CABF374B6834');
        $this->addSql('ALTER TABLE booking_request DROP FOREIGN KEY FK_6129CABFC75F82BB');
        $this->addSql('ALTER TABLE booking_request ADD CONSTRAINT FK_6129CABF374B6834 FOREIGN KEY (pet_owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE booking_request ADD CONSTRAINT FK_6129CABFC75F82BB FOREIGN KEY (groomer_id) REFERENCES groomer_profile (id)');
        $this->addSql('ALTER TABLE booking_request RENAME INDEX idx_booking_request_service TO IDX_6129CABFED5CA9E6');
        $this->addSql('CREATE INDEX idx_city_slug ON city (slug)');
        $this->addSql('ALTER TABLE groomer_profile DROP FOREIGN KEY FK_944EA9A78BAC62AF');
        $this->addSql('ALTER TABLE groomer_profile DROP FOREIGN KEY FK_944EA9A7A76ED395');
        $this->addSql('ALTER TABLE groomer_profile CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE groomer_profile ADD CONSTRAINT FK_944EA9A78BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE groomer_profile ADD CONSTRAINT FK_944EA9A7A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE groomer_profile RENAME INDEX uniq_groomer_profile_slug TO UNIQ_944EA9A7989D9B62');
        $this->addSql('ALTER TABLE groomer_profile RENAME INDEX idx_944ea9a78bac62af TO idx_groomer_profile_city');
        $this->addSql('ALTER TABLE groomer_profile_service RENAME INDEX idx_groomer_profile_service_groomer_profile_id TO IDX_F6BAE94B54D7E576');
        $this->addSql('ALTER TABLE groomer_profile_service RENAME INDEX idx_groomer_profile_service_service_id TO IDX_F6BAE94BED5CA9E6');
        $this->addSql('CREATE INDEX idx_review_rating ON review (rating)');
        $this->addSql('ALTER TABLE seo_content DROP FOREIGN KEY FK_57FC0AE28BAC62AF');
        $this->addSql('ALTER TABLE seo_content DROP FOREIGN KEY FK_57FC0AE2ED5CA9E6');
        $this->addSql('ALTER TABLE seo_content ADD CONSTRAINT FK_57FC0AE28BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE seo_content ADD CONSTRAINT FK_57FC0AE2ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('CREATE INDEX idx_service_slug ON service (slug)');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            $this->skipIf(true, sprintf('Skipping MySQL-specific migration on %s', $platform));
            return;
        }
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lead_capture DROP FOREIGN KEY FK_D230FA8B8BAC62AF');
        $this->addSql('ALTER TABLE lead_capture DROP FOREIGN KEY FK_D230FA8BED5CA9E6');
        $this->addSql('DROP TABLE lead_capture');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE booking_request DROP FOREIGN KEY FK_6129CABFC75F82BB');
        $this->addSql('ALTER TABLE booking_request DROP FOREIGN KEY FK_6129CABF374B6834');
        $this->addSql('ALTER TABLE booking_request ADD CONSTRAINT FK_6129CABFC75F82BB FOREIGN KEY (groomer_id) REFERENCES groomer_profile (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booking_request ADD CONSTRAINT FK_6129CABF374B6834 FOREIGN KEY (pet_owner_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booking_request RENAME INDEX idx_6129cabfed5ca9e6 TO idx_booking_request_service');
        $this->addSql('DROP INDEX idx_city_slug ON city');
        $this->addSql('DROP INDEX idx_blog_post_slugs_slug ON blog_post_slugs');
        $this->addSql('ALTER TABLE blog_post_slugs RENAME INDEX uniq_c9f7fce2989d9b62 TO UNIQ_BLOG_POST_SLUGS_SLUG');
        $this->addSql('ALTER TABLE blog_post_slugs RENAME INDEX idx_c9f7fce24b89032c TO IDX_BLOG_POST_SLUGS_POST_ID');
        $this->addSql('ALTER TABLE groomer_profile DROP FOREIGN KEY FK_944EA9A7A76ED395');
        $this->addSql('ALTER TABLE groomer_profile DROP FOREIGN KEY FK_944EA9A78BAC62AF');
        $this->addSql('ALTER TABLE groomer_profile CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE groomer_profile ADD CONSTRAINT FK_944EA9A7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groomer_profile ADD CONSTRAINT FK_944EA9A78BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groomer_profile RENAME INDEX uniq_944ea9a7989d9b62 TO UNIQ_GROOMER_PROFILE_SLUG');
        $this->addSql('ALTER TABLE groomer_profile RENAME INDEX idx_groomer_profile_city TO IDX_944EA9A78BAC62AF');
        $this->addSql('ALTER TABLE groomer_profile_service RENAME INDEX idx_f6bae94b54d7e576 TO IDX_GROOMER_PROFILE_SERVICE_GROOMER_PROFILE_ID');
        $this->addSql('ALTER TABLE groomer_profile_service RENAME INDEX idx_f6bae94bed5ca9e6 TO IDX_GROOMER_PROFILE_SERVICE_SERVICE_ID');
        $this->addSql('DROP INDEX idx_review_rating ON review');
        $this->addSql('ALTER TABLE seo_content DROP FOREIGN KEY FK_57FC0AE28BAC62AF');
        $this->addSql('ALTER TABLE seo_content DROP FOREIGN KEY FK_57FC0AE2ED5CA9E6');
        $this->addSql('ALTER TABLE seo_content ADD CONSTRAINT FK_57FC0AE28BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE seo_content ADD CONSTRAINT FK_57FC0AE2ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_service_slug ON service');
    }
}
