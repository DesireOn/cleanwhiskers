<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250907110040_CreateEmailSuppression extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create email_suppression table';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('email_suppression')) {
            return;
        }

        $table = $schema->createTable('email_suppression');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('email', Types::STRING, ['length' => 255]);
        $table->addColumn('reason', Types::STRING, ['length' => 255, 'notnull' => false]);
        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email'], 'uniq_email_suppression_email');
    }
}

