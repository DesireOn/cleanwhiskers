# CleanWhiskers

This project is a Symfony application.

## Deployment

The deployment workflows ensure required runtime directories exist and are writable:

```
mkdir -p var/cache var/log public/build
chown -R www-data:www-data var public/build
```

Adjust the user and group as needed for your environment.

## Data Retention

- Command: `bin/console app:leads:retention`
- Purpose: deletes unclaimed leads older than 90 days and anonymizes claimed leads older than 1 year. Actions are recorded in `audit_log` as `retention_purge` and `retention_anonymize`.
- CRON example (runs nightly at 02:30):
  - `30 2 * * * cd /var/www/cleanwhiskers && /usr/bin/php bin/console app:leads:retention --env=prod >> var/log/retention.log 2>&1`
