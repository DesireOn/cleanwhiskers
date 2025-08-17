.PHONY: setup test

setup:
	composer install --no-interaction --prefer-dist

test:
	composer ci
