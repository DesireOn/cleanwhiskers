<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241005120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema for users, groomers, services, cities, booking requests and reviews';
    }

    public function up(Schema $schema): void
    {
        // user
        $users = $schema->createTable('user');
        $users->addColumn('id', 'integer', ['autoincrement' => true]);
        $users->addColumn('email', 'string', ['length' => 180]);
        $users->addColumn('password', 'string', ['length' => 255]);
        $users->addColumn('roles', 'json');
        $users->addColumn('created_at', 'datetime_immutable');
        $users->setPrimaryKey(['id']);
        $users->addUniqueIndex(['email']);

        // city
        $city = $schema->createTable('city');
        $city->addColumn('id', 'integer', ['autoincrement' => true]);
        $city->addColumn('name', 'string', ['length' => 255]);
        $city->addColumn('slug', 'string', ['length' => 255]);
        $city->addColumn('state', 'string', ['length' => 255, 'notnull' => false]);
        $city->addColumn('country', 'string', ['length' => 2]);
        $city->addColumn('is_active', 'boolean');
        $city->setPrimaryKey(['id']);
        $city->addUniqueIndex(['slug']);

        // service
        $service = $schema->createTable('service');
        $service->addColumn('id', 'integer', ['autoincrement' => true]);
        $service->addColumn('name', 'string', ['length' => 255]);
        $service->addColumn('slug', 'string', ['length' => 255]);
        $service->addColumn('is_active', 'boolean');
        $service->setPrimaryKey(['id']);
        $service->addUniqueIndex(['slug']);

        // groomer_profile
        $gp = $schema->createTable('groomer_profile');
        $gp->addColumn('id', 'integer', ['autoincrement' => true]);
        $gp->addColumn('user_id', 'integer');
        $gp->addColumn('display_name', 'string', ['length' => 255]);
        $gp->addColumn('slug', 'string', ['length' => 255]);
        $gp->addColumn('city_id', 'integer', ['notnull' => false]);
        $gp->addColumn('description', 'text');
        $gp->addColumn('rating_avg', 'float', ['notnull' => false]);
        $gp->addColumn('rating_count', 'integer');
        $gp->addColumn('is_active', 'boolean');
        $gp->addColumn('created_at', 'datetime_immutable');
        $gp->addColumn('updated_at', 'datetime_immutable', ['notnull' => false]);
        $gp->addColumn('address', 'string', ['length' => 255, 'notnull' => false]);
        $gp->addColumn('phone', 'string', ['length' => 50, 'notnull' => false]);
        $gp->addColumn('website', 'string', ['length' => 255, 'notnull' => false]);
        $gp->setPrimaryKey(['id']);
        $gp->addUniqueIndex(['slug']);
        $gp->addUniqueIndex(['user_id']);
        $gp->addIndex(['city_id']);
        $gp->addForeignKeyConstraint('user', ['user_id'], ['id'], ['onDelete' => 'CASCADE']);
        $gp->addForeignKeyConstraint('city', ['city_id'], ['id'], ['onDelete' => 'SET NULL']);

        // groomer_service join table
        $gs = $schema->createTable('groomer_service');
        $gs->addColumn('groomer_profile_id', 'integer');
        $gs->addColumn('service_id', 'integer');
        $gs->setPrimaryKey(['groomer_profile_id', 'service_id']);
        $gs->addIndex(['service_id']);
        $gs->addForeignKeyConstraint('groomer_profile', ['groomer_profile_id'], ['id'], ['onDelete' => 'CASCADE']);
        $gs->addForeignKeyConstraint('service', ['service_id'], ['id'], ['onDelete' => 'CASCADE']);

        // booking_request
        $br = $schema->createTable('booking_request');
        $br->addColumn('id', 'integer', ['autoincrement' => true]);
        $br->addColumn('pet_owner_id', 'integer');
        $br->addColumn('groomer_id', 'integer');
        $br->addColumn('status', 'string', ['length' => 20]);
        $br->addColumn('message', 'text');
        $br->addColumn('preferred_at', 'datetime_immutable', ['notnull' => false]);
        $br->addColumn('created_at', 'datetime_immutable');
        $br->setPrimaryKey(['id']);
        $br->addForeignKeyConstraint('user', ['pet_owner_id'], ['id'], ['onDelete' => 'CASCADE']);
        $br->addForeignKeyConstraint('groomer_profile', ['groomer_id'], ['id'], ['onDelete' => 'CASCADE']);

        // review
        $review = $schema->createTable('review');
        $review->addColumn('id', 'integer', ['autoincrement' => true]);
        $review->addColumn('groomer_id', 'integer');
        $review->addColumn('author_id', 'integer');
        $review->addColumn('rating', 'integer');
        $review->addColumn('comment', 'text', ['notnull' => false]);
        $review->addColumn('created_at', 'datetime_immutable');
        $review->addColumn('booking_request_id', 'integer', ['notnull' => false]);
        $review->setPrimaryKey(['id']);
        $review->addForeignKeyConstraint('groomer_profile', ['groomer_id'], ['id'], ['onDelete' => 'CASCADE']);
        $review->addForeignKeyConstraint('user', ['author_id'], ['id'], ['onDelete' => 'CASCADE']);
        $review->addForeignKeyConstraint('booking_request', ['booking_request_id'], ['id'], ['onDelete' => 'SET NULL']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('review');
        $schema->dropTable('booking_request');
        $schema->dropTable('groomer_service');
        $schema->dropTable('groomer_profile');
        $schema->dropTable('service');
        $schema->dropTable('city');
        $schema->dropTable('user');
    }
}
