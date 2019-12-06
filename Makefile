.DEFAULT_GOAL:help

DC=docker-compose -f docker/docker-compose.yml

.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m * make %s\033[0m ## %s\n", $$1, $$2}'

.PHONY: init
init: config/burrow-cli-config.php ## Init config

config/burrow-cli-config.php:
	cp config/burrow-cli-config.php.dev config/burrow-cli-config.php

.PHONY: build
build: ## Build image used to install dependencies and run tests
	$(DC) build

vendor: composer.json ## Install dependencies
	$(DC) run -T php composer install

.PHONY: install
install: vendor ## Install dependencies

.PHONY: test-integration
test-integration: vendor ## run integration tests (tests/integration)
	$(DC) run -T php-amqp-php-lib sh -c "dockerize -wait tcp://rabbitmq:5672 && vendor/bin/phpunit --config=phpunit-integration.xml"
	$(DC) run -T php-pecl-ext sh -c "dockerize -wait tcp://rabbitmq:5672 && vendor/bin/phpunit --config=phpunit-integration.xml"

.PHONY: test-unit
test-unit: vendor ## run unit tests (tests/unit)
	$(DC) run -T php vendor/bin/phpunit --config=phpunit.xml

.PHONY: test-validation
test-validation: vendor ## run validation tests (tests/validation)
	$(DC) run -T php sh -c "dockerize -wait tcp://rabbitmq:5672 && vendor/bin/phpunit --config=phpunit-validation.xml"

.PHONY: lint
lint: ## Fix codestyle
	$(DC) run -T php vendor/bin/phpcbf --standard=PSR2 src