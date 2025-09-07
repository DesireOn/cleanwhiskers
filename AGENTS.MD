Purpose
This file tells any assistant (and humans!) exactly how to build, run, test, and contribute to this Symfony MVC + MySQL app. Follow it to keep the codebase clean, readable, and consistent.

Before making any code change, you MUST load full project context just like the default behavior when no AGENTS.md is present. Do not rely solely on this document’s summaries—always scan the entire repository.

Environment
PHP: 8.2+

Symfony: 6.4 LTS or 7.x (project uses symfony/framework-bundle)

DB: MySQL 8.x

Composer: 2.x

Node/Yarn (optional): only if frontend assets exist

CLI tools: symfony CLI recommended

Never commit real secrets. Use .env.local (ignored) for local overrides. Keep .env as template defaults.

Quick Start
bash
Copy
Edit
# 1) Install PHP deps
composer install

# 2) Copy env and set DATABASE_URL (use MySQL)
cp .env .env.local
# edit .env.local, e.g. DATABASE_URL="mysql://user:pass@127.0.0.1:3306/app?serverVersion=8.0"

# 3) Start DB (if using Docker)
docker compose up -d db  # optional

# 4) Prepare DB
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction --purge-with-truncate || true

# 5) Run app
symfony serve -d  # or php -S localhost:8000 -t public
Tests (use separate test DB):

bash
Copy
Edit
# creates test db if missing
APP_ENV=test php bin/console doctrine:database:create --if-not-exists
APP_ENV=test php bin/console doctrine:migrations:migrate --no-interaction
APP_ENV=test ./vendor/bin/phpunit
Composer Scripts (used by CI & Codex)
Add these to composer.json (if not present):

json
Copy
Edit
{
  "scripts": {
    "lint:php": "PHP_CS_FIXER_IGNORE_ENV=1 php-cs-fixer fix --dry-run --diff",
    "fix:php": "PHP_CS_FIXER_IGNORE_ENV=1 php-cs-fixer fix --diff",
    "stan": "phpstan analyse --memory-limit=1G",
    "audit": "composer audit --no-interaction || true",
    "migrate": "php bin/console doctrine:migrations:migrate --no-interaction",
    "db:reset": [
      "@php bin/console doctrine:schema:drop --force --full-database --no-interaction",
      "@php bin/console doctrine:database:create --if-not-exists",
      "@php bin/console doctrine:migrations:migrate --no-interaction",
      "@php bin/console doctrine:fixtures:load --no-interaction --purge-with-truncate || true"
    ],
    "test": [
      "APP_ENV=test php bin/console doctrine:database:create --if-not-exists",
      "APP_ENV=test php bin/console doctrine:migrations:migrate --no-interaction",
      "APP_ENV=test php -d zend.enable_gc=0 ./vendor/bin/phpunit --testdox"
    ],
    "ci": [
      "@lint:php",
      "@stan",
      "@test",
      "@audit"
    ]
  }
}
Install recommended dev tools:

bash
Copy
Edit
composer require --dev friendsofphp/php-cs-fixer phpstan/phpstan phpunit/phpunit symfony/test-pack doctrine/doctrine-fixtures-bundle
Optional (but great):

bash
Copy
Edit
composer require --dev rector/rector vimeo/psalm phpro/grumphp
Project Structure (conventions)
Domain logic: src/ (Controllers thin; Services/Handlers do work)

HTTP: Controllers in src/Controller, routes via attributes

Data: Doctrine entities in src/Entity, repositories in src/Repository

Migrations: migrations/

Config: config/

Templates: templates/ (Twig)

Assets: JS/CSS sources live in assets/ and are compiled into public/ with `php bin/console asset-map:compile`; reference them in Twig using `asset()`.

Tests: tests/ mirrors src/ (Unit, Integration, Functional)

Coding Standards
PSR-12 + Symfony best practices.

Always start PHP files with declare(strict_types=1);

Prefer constructor injection for services.

Return DTOs or typed arrays at boundaries; avoid mixed.

Controllers: validate input (Symfony Validator), delegate to services, return typed responses.

Naming: explicit, intention-revealing; avoid abbreviations.

Exceptions over nulls. Use domain exceptions; map to HTTP in an exception listener.

No static state (except constants/factories without state).

Database:

Use Doctrine migrations for schema changes.

Keep entities lean; put domain behavior in services when unsure.

Immutability where reasonable; use value objects (e.g., Email, Money) for critical types.

Comments: only when the code can’t be made clearer; otherwise refactor.

Tests
Unit tests: fast, pure PHP.

Integration/Functional: kernel boot with SQLite-in-memory or MySQL test DB.

Fixtures: minimal; use factories/data builders in tests.

Cover:

Happy path + edge cases

Error handling (exceptions mapped to proper HTTP codes)

Repositories (basic CRUD)

Aim for 70%+ coverage on changed code; quality over raw %.

Run all tests locally:

bash
Copy
Edit
composer ci
Security & Quality
Never commit secrets. Use .env.local, .env.test.local.

Input validation & sanitization via Symfony Validator and ParamConverter DTOs.

CSRF on forms; CORS configured explicitly.

Headers: add sensible security headers via nelmio/security-bundle (optional).

Dependency checks: composer audit in CI.

Logs: use monolog channels; no PII in logs.

Migrations: every schema change must include a migration.

Performance: add indexes; avoid N+1s (->addSelect, fetch joins).

Git & CI
Flow: GitHub Flow (feature branch → PR → review → merge). Small PRs.

Commits: Conventional Commits (feat:, fix:, refactor:, test:, chore:).

CI gate (required to pass): composer ci

php-cs-fixer (dry run)

phpstan

phpunit (test env)

composer audit

Tasks for Automated Assistants (Codex)
When working on an issue:

Allowed files/folders: src/, tests/, templates/, config/, migrations/, composer.json, .php-cs-fixer.php, phpstan.neon, phpunit.xml.dist

Forbidden: real secrets, CI config changes, infra/terraform, deployment keys.

Must do before commit:

composer fix:php (or ensure lint:php passes)

composer stan

composer test

php bin/console asset-map:compile

For any new JS/CSS intended for the browser, place the source under assets/, run the compile command above, and commit the generated file in public/.
When referencing these assets in Twig, load ES modules with `type="module"` and verify each referenced file exists to avoid missing scripts.

Before completing a task, run all PHPUnit tests using the command:

    ./vendor/bin/phpunit --log-junit /tmp/phpunit.log

If any test fails:
- DO NOT mark the task as complete.
- Read the error(s), fix the code, and re-run the test.
- Repeat this process until ALL tests pass.
Only mark the task as complete when the PHPUnit test suite returns 100% success.


If DB change is required: generate a migration and include it in the PR.

Migrations policy (assistants and humans):
- Always generate migrations via `php bin/console make:migration`.
- Do NOT hand-write or edit files in `migrations/` via patches; use the command above.
- If the command cannot be executed in the current environment, request the user to run it and commit the generated file.
- Apply migrations with `php bin/console doctrine:migrations:migrate --no-interaction` when validating changes locally/CI.

Definition of Done (DoD)
 Coding standards (PSR-12) pass: composer lint:php

 Static analysis clean: composer stan

 Tests added/updated and green: composer test

 Migrations/fixtures included (when applicable)

 Public API/Routes documented (if added/changed)

 Security, perf, and error handling considered

 Static asset links (CSS/JS) verified to point to existing files to prevent 404s

 Asset map compiles without missing references: php bin/console asset-map:compile

 PR template completed


Useful Commands
bash
Copy
Edit
# Fix code style
composer fix:php

# Static analysis
composer stan

# Run full CI locally
composer ci

# Make a new migration
php bin/console make:migration && composer migrate
