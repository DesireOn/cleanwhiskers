<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250907083546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE audit_log (id INT AUTO_INCREMENT NOT NULL, event VARCHAR(64) NOT NULL, actor_type VARCHAR(16) NOT NULL, actor_id INT DEFAULT NULL, subject_type VARCHAR(16) NOT NULL, subject_id INT NOT NULL, metadata JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_audit_event (event), INDEX idx_audit_subject (subject_type, subject_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blog_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_72113DE6989D9B62 (slug), INDEX idx_blog_category_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blog_post (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, title VARCHAR(255) NOT NULL, excerpt LONGTEXT DEFAULT NULL, content_html LONGTEXT NOT NULL, cover_image_path VARCHAR(255) DEFAULT NULL, is_published TINYINT(1) NOT NULL, published_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', canonical_url VARCHAR(255) DEFAULT NULL, meta_title VARCHAR(255) DEFAULT NULL, meta_description VARCHAR(255) DEFAULT NULL, reading_minutes SMALLINT DEFAULT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_BA5AE01D989D9B62 (slug), INDEX IDX_BA5AE01D12469DE2 (category_id), INDEX idx_blog_post_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blog_post_blog_tag (blog_post_id INT NOT NULL, blog_tag_id INT NOT NULL, INDEX IDX_CA877A44A77FBEAF (blog_post_id), INDEX IDX_CA877A442F9DC6D0 (blog_tag_id), PRIMARY KEY(blog_post_id, blog_tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blog_post_slugs (id INT AUTO_INCREMENT NOT NULL, post_id INT NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_C9F7FCE2989D9B62 (slug), INDEX IDX_C9F7FCE24B89032C (post_id), INDEX idx_blog_post_slugs_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blog_tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_6EC3989989D9B62 (slug), INDEX idx_blog_tag_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking_request (id INT AUTO_INCREMENT NOT NULL, groomer_id INT NOT NULL, pet_owner_id INT NOT NULL, service_id INT DEFAULT NULL, status VARCHAR(20) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', notes LONGTEXT DEFAULT NULL, INDEX IDX_6129CABFED5CA9E6 (service_id), INDEX idx_booking_request_groomer (groomer_id), INDEX idx_booking_request_pet_owner (pet_owner_id), INDEX idx_booking_request_status (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, seo_intro LONGTEXT DEFAULT NULL, coverage_notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2D5B0234989D9B62 (slug), INDEX idx_city_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email_suppression (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, reason VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_email_suppression_email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groomer_profile (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, city_id INT NOT NULL, business_name VARCHAR(255) NOT NULL, about LONGTEXT NOT NULL, service_area VARCHAR(120) DEFAULT NULL, phone VARCHAR(32) DEFAULT NULL, services_offered LONGTEXT DEFAULT NULL, price INT DEFAULT NULL, badges JSON DEFAULT NULL, specialties JSON DEFAULT NULL, outreach_email VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_944EA9A7989D9B62 (slug), INDEX IDX_944EA9A7A76ED395 (user_id), INDEX idx_groomer_profile_slug (slug), INDEX idx_groomer_profile_city (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groomer_profile_service (groomer_profile_id INT NOT NULL, service_id INT NOT NULL, INDEX IDX_F6BAE94B54D7E576 (groomer_profile_id), INDEX IDX_F6BAE94BED5CA9E6 (service_id), PRIMARY KEY(groomer_profile_id, service_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lead_capture (id INT AUTO_INCREMENT NOT NULL, city_id INT NOT NULL, service_id INT NOT NULL, claimed_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(32) DEFAULT NULL, pet_type VARCHAR(50) DEFAULT NULL, dog_breed VARCHAR(255) DEFAULT NULL, consent_to_share TINYINT(1) DEFAULT 0 NOT NULL, status VARCHAR(20) DEFAULT \'pending\' NOT NULL, claimed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', owner_token_hash VARCHAR(255) NOT NULL, owner_token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D230FA8B8BAC62AF (city_id), INDEX IDX_D230FA8BED5CA9E6 (service_id), INDEX IDX_D230FA8BF67E7A38 (claimed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lead_recipient (id INT AUTO_INCREMENT NOT NULL, lead_id INT NOT NULL, groomer_profile_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, status VARCHAR(20) DEFAULT \'queued\' NOT NULL, invite_sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', claim_token_hash VARCHAR(255) NOT NULL, token_expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F1A7F97155458D (lead_id), INDEX IDX_F1A7F97154D7E576 (groomer_profile_id), UNIQUE INDEX uniq_lead_recipient_lead_email (lead_id, email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, groomer_id INT NOT NULL, author_id INT NOT NULL, rating INT NOT NULL, comment LONGTEXT NOT NULL, verified TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_794381C6C75F82BB (groomer_id), INDEX IDX_794381C6F675F31B (author_id), INDEX idx_review_groomer_created_at (groomer_id, created_at), INDEX idx_review_rating (rating), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_E19D9AD2989D9B62 (slug), INDEX idx_service_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE testimonial (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, quote LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_testimonial_created_at (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blog_post ADD CONSTRAINT FK_BA5AE01D12469DE2 FOREIGN KEY (category_id) REFERENCES blog_category (id)');
        $this->addSql('ALTER TABLE blog_post_blog_tag ADD CONSTRAINT FK_CA877A44A77FBEAF FOREIGN KEY (blog_post_id) REFERENCES blog_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_post_blog_tag ADD CONSTRAINT FK_CA877A442F9DC6D0 FOREIGN KEY (blog_tag_id) REFERENCES blog_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_post_slugs ADD CONSTRAINT FK_C9F7FCE24B89032C FOREIGN KEY (post_id) REFERENCES blog_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booking_request ADD CONSTRAINT FK_6129CABFC75F82BB FOREIGN KEY (groomer_id) REFERENCES groomer_profile (id)');
        $this->addSql('ALTER TABLE booking_request ADD CONSTRAINT FK_6129CABF374B6834 FOREIGN KEY (pet_owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE booking_request ADD CONSTRAINT FK_6129CABFED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE groomer_profile ADD CONSTRAINT FK_944EA9A7A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE groomer_profile ADD CONSTRAINT FK_944EA9A78BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE groomer_profile_service ADD CONSTRAINT FK_F6BAE94B54D7E576 FOREIGN KEY (groomer_profile_id) REFERENCES groomer_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groomer_profile_service ADD CONSTRAINT FK_F6BAE94BED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lead_capture ADD CONSTRAINT FK_D230FA8B8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE lead_capture ADD CONSTRAINT FK_D230FA8BED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE lead_capture ADD CONSTRAINT FK_D230FA8BF67E7A38 FOREIGN KEY (claimed_by_id) REFERENCES groomer_profile (id)');
        $this->addSql('ALTER TABLE lead_recipient ADD CONSTRAINT FK_F1A7F97155458D FOREIGN KEY (lead_id) REFERENCES lead_capture (id)');
        $this->addSql('ALTER TABLE lead_recipient ADD CONSTRAINT FK_F1A7F97154D7E576 FOREIGN KEY (groomer_profile_id) REFERENCES groomer_profile (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6C75F82BB FOREIGN KEY (groomer_id) REFERENCES groomer_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog_post DROP FOREIGN KEY FK_BA5AE01D12469DE2');
        $this->addSql('ALTER TABLE blog_post_blog_tag DROP FOREIGN KEY FK_CA877A44A77FBEAF');
        $this->addSql('ALTER TABLE blog_post_blog_tag DROP FOREIGN KEY FK_CA877A442F9DC6D0');
        $this->addSql('ALTER TABLE blog_post_slugs DROP FOREIGN KEY FK_C9F7FCE24B89032C');
        $this->addSql('ALTER TABLE booking_request DROP FOREIGN KEY FK_6129CABFC75F82BB');
        $this->addSql('ALTER TABLE booking_request DROP FOREIGN KEY FK_6129CABF374B6834');
        $this->addSql('ALTER TABLE booking_request DROP FOREIGN KEY FK_6129CABFED5CA9E6');
        $this->addSql('ALTER TABLE groomer_profile DROP FOREIGN KEY FK_944EA9A7A76ED395');
        $this->addSql('ALTER TABLE groomer_profile DROP FOREIGN KEY FK_944EA9A78BAC62AF');
        $this->addSql('ALTER TABLE groomer_profile_service DROP FOREIGN KEY FK_F6BAE94B54D7E576');
        $this->addSql('ALTER TABLE groomer_profile_service DROP FOREIGN KEY FK_F6BAE94BED5CA9E6');
        $this->addSql('ALTER TABLE lead_capture DROP FOREIGN KEY FK_D230FA8B8BAC62AF');
        $this->addSql('ALTER TABLE lead_capture DROP FOREIGN KEY FK_D230FA8BED5CA9E6');
        $this->addSql('ALTER TABLE lead_capture DROP FOREIGN KEY FK_D230FA8BF67E7A38');
        $this->addSql('ALTER TABLE lead_recipient DROP FOREIGN KEY FK_F1A7F97155458D');
        $this->addSql('ALTER TABLE lead_recipient DROP FOREIGN KEY FK_F1A7F97154D7E576');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6C75F82BB');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6F675F31B');
        $this->addSql('DROP TABLE audit_log');
        $this->addSql('DROP TABLE blog_category');
        $this->addSql('DROP TABLE blog_post');
        $this->addSql('DROP TABLE blog_post_blog_tag');
        $this->addSql('DROP TABLE blog_post_slugs');
        $this->addSql('DROP TABLE blog_tag');
        $this->addSql('DROP TABLE booking_request');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE email_suppression');
        $this->addSql('DROP TABLE groomer_profile');
        $this->addSql('DROP TABLE groomer_profile_service');
        $this->addSql('DROP TABLE lead_capture');
        $this->addSql('DROP TABLE lead_recipient');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE testimonial');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
