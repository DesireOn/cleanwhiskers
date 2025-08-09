# CleanWhiskers

This project is a Symfony application.

## Deployment

The deployment workflows ensure required runtime directories exist and are writable:

```
mkdir -p var/cache var/log public/build public/assets public/bundles assets/vendor
chown -R www-data:www-data var public/build public/assets public/bundles assets/vendor
```

Adjust the user and group as needed for your environment.

