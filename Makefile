#!make

.PHONY: tests

COMPOSE_IMAGE = composer:2
PHP_IMAGE=php:8

composer:
	docker pull $(COMPOSE_IMAGE)
	docker run --rm --interactive --tty --user $(id -u):$(id -g) -w /app --volume `pwd`:/app $(COMPOSE_IMAGE) composer $(P)

composer_install:
	make composer P="install"

phpunit:
	docker pull $(PHP_IMAGE)
	docker run --rm -it --user $(id -u):$(id -g) -w /app --volume `pwd`:/app $(PHP_IMAGE) ./vendor/bin/phpunit $(P)

# run all php unit tests
tests:
	make phpunit P=tests
