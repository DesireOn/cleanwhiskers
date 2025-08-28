<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250810180002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add blog_post_slugs table to track historical slugs';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('blog_post_slugs');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('post_id', Types::INTEGER);
        $table->addColumn('slug', Types::STRING, ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['slug'], 'UNIQ_BLOG_POST_SLUGS_SLUG');
        $table->addIndex(['post_id'], 'IDX_BLOG_POST_SLUGS_POST_ID');
        // FK added later to avoid dependency on blog_post creation order
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('blog_post_slugs');
    }
}
