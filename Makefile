.PHONY: setup test php-test python-lint python-test

setup:
	composer install --no-interaction --prefer-dist
	pip install --requirement requirements-dev.txt

php-test:
	composer ci

python-lint:
	ruff check scripts tests/scripts

python-test:
	pytest

test: php-test python-lint python-test
