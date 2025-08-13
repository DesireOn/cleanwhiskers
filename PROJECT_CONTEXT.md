# Project Context: CleanWhiskers

## Overview
CleanWhiskers is an early-stage Symfony application. The codebase currently exposes a single homepage route and contains scaffolding for future API resources, entities, and fixtures.

## Tech Stack
- PHP ≥ 8.2
- Symfony 7.3 framework with API Platform and Doctrine ORM
- Twig templating engine
- Composer for dependency management
- PHPUnit, PHPStan, and PHP-CS-Fixer configured for quality control

## Current Features
- `HomeController` maps `/` to `templates/home/index.html.twig` with a city/service search form.
- `City` entity stores a city's name, unique slug, and creation timestamp, accessed via `CityRepository::findOneBySlug()`.
- `AppFixtures` class exists but loads no data.

## Development Setup
1. Install dependencies: `composer install`
2. Copy environment file and configure database: `cp .env .env.local`
3. Prepare database: run Doctrine create/migrate and fixtures commands
4. Start application: `symfony serve -d` or `php -S localhost:8000 -t public`

## Testing & QA
- Quality scripts are expected: `composer lint:php`, `composer stan`, and `composer test`
- Unit and integration tests cover the `City` entity and repository

## Notes
- `PROJECT_CONTEXT.md` should be updated whenever project state changes
- Follow PSR-12 and Symfony best practices; run provided Composer scripts before commits
