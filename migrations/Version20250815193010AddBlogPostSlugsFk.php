<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250815193010AddBlogPostSlugsFk extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add FK blog_post_slugs(post_id) -> blog_post(id), idempotent';
    }

    public function up(Schema $schema): void
    {
        // Only add the FK if both tables exist and the FK is not already present
        $sql = <<<'SQL'
SELECT COUNT(*)
FROM information_schema.KEY_COLUMN_USAGE k
WHERE k.TABLE_SCHEMA = DATABASE()
  AND k.TABLE_NAME = 'blog_post_slugs'
  AND k.COLUMN_NAME = 'post_id'
  AND k.REFERENCED_TABLE_NAME = 'blog_post'
  AND k.REFERENCED_COLUMN_NAME = 'id';
SQL;

        $exists = (int) $this->connection->fetchOne($sql);

        // Ensure the table exists before attempting to add the FK
        $hasSlugs = $schema->hasTable('blog_post_slugs');
        $hasPost = $schema->hasTable('blog_post');

        if ($hasSlugs && $hasPost && $exists === 0) {
            // Choose a deterministic constraint name
            $this->addSql('ALTER TABLE blog_post_slugs ADD CONSTRAINT FK_BLOG_POST_SLUGS_POST_ID FOREIGN KEY (post_id) REFERENCES blog_post (id) ON DELETE CASCADE');
        }
    }

    public function down(Schema $schema): void
    {
        // Drop the FK if present
        $sql = <<<'SQL'
SELECT CONSTRAINT_NAME
FROM information_schema.KEY_COLUMN_USAGE k
WHERE k.TABLE_SCHEMA = DATABASE()
  AND k.TABLE_NAME = 'blog_post_slugs'
  AND k.COLUMN_NAME = 'post_id'
  AND k.REFERENCED_TABLE_NAME = 'blog_post'
  AND k.REFERENCED_COLUMN_NAME = 'id'
LIMIT 1;
SQL;

        $constraintName = $this->connection->fetchOne($sql);
        if (is_string($constraintName) && $constraintName !== '') {
            $this->addSql(sprintf('ALTER TABLE blog_post_slugs DROP FOREIGN KEY %s', $constraintName));
        }
    }
}
