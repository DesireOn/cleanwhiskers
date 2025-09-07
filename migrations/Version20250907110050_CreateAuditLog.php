<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250907110050_CreateAuditLog extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create audit_log table with indexes';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('audit_log')) {
            return;
        }

        $table = $schema->createTable('audit_log');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('event', Types::STRING, ['length' => 64]);
        $table->addColumn('actor_type', Types::STRING, ['length' => 16]);
        $table->addColumn('actor_id', Types::INTEGER, ['notnull' => false]);
        $table->addColumn('subject_type', Types::STRING, ['length' => 16]);
        $table->addColumn('subject_id', Types::INTEGER);
        $table->addColumn('metadata', Types::JSON);
        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['event'], 'idx_audit_event');
        $table->addIndex(['subject_type', 'subject_id'], 'idx_audit_subject');
    }
}

