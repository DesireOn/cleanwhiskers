<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250815193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create blog tables';
    }

    public function up(Schema $schema): void
    {
        $category = $schema->createTable('blog_category');
        $category->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $category->addColumn('name', Types::STRING, ['length' => 255]);
        $category->addColumn('slug', Types::STRING, ['length' => 255]);
        $category->setPrimaryKey(['id']);
        $category->addUniqueIndex(['slug']);
        $category->addIndex(['slug'], 'idx_blog_category_slug');

        $tag = $schema->createTable('blog_tag');
        $tag->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $tag->addColumn('name', Types::STRING, ['length' => 255]);
        $tag->addColumn('slug', Types::STRING, ['length' => 255]);
        $tag->setPrimaryKey(['id']);
        $tag->addUniqueIndex(['slug']);
        $tag->addIndex(['slug'], 'idx_blog_tag_slug');

        $post = $schema->createTable('blog_post');
        $post->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $post->addColumn('category_id', Types::INTEGER);
        $post->addColumn('title', Types::STRING, ['length' => 255]);
        $post->addColumn('excerpt', Types::TEXT, ['notnull' => false]);
        $post->addColumn('content_html', Types::TEXT);
        $post->addColumn('cover_image_path', Types::STRING, ['length' => 255, 'notnull' => false]);
        $post->addColumn('is_published', Types::BOOLEAN);
        $post->addColumn('published_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $post->addColumn('updated_at', Types::DATETIME_IMMUTABLE);
        $post->addColumn('canonical_url', Types::STRING, ['length' => 255, 'notnull' => false]);
        $post->addColumn('meta_title', Types::STRING, ['length' => 255, 'notnull' => false]);
        $post->addColumn('meta_description', Types::STRING, ['length' => 255, 'notnull' => false]);
        $post->addColumn('reading_minutes', Types::SMALLINT, ['notnull' => false]);
        $post->addColumn('slug', Types::STRING, ['length' => 255]);
        $post->setPrimaryKey(['id']);
        $post->addUniqueIndex(['slug']);
        $post->addIndex(['slug'], 'idx_blog_post_slug');
        $post->addIndex(['category_id']);
        $post->addForeignKeyConstraint('blog_category', ['category_id'], ['id']);

        $postTag = $schema->createTable('blog_post_blog_tag');
        $postTag->addColumn('blog_post_id', Types::INTEGER);
        $postTag->addColumn('blog_tag_id', Types::INTEGER);
        $postTag->setPrimaryKey(['blog_post_id', 'blog_tag_id']);
        $postTag->addIndex(['blog_post_id']);
        $postTag->addIndex(['blog_tag_id']);
        $postTag->addForeignKeyConstraint('blog_post', ['blog_post_id'], ['id'], ['onDelete' => 'CASCADE']);
        $postTag->addForeignKeyConstraint('blog_tag', ['blog_tag_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('blog_post_blog_tag');
        $schema->dropTable('blog_post');
        $schema->dropTable('blog_tag');
        $schema->dropTable('blog_category');
    }
}
