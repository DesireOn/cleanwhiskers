setup:
	composer install

test:
	composer test

seed-staging:
	APP_ENV=staging bin/console app:seed-staging
