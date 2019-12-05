.DEFAULT_GOAL:help

DC=docker-compose -f docker/docker-compose.yml

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m * make %s\033[0m ## %s\n", $$1, $$2}'

init: ## Init config
	cp config/burrow-cli-config.php.dev config/burrow-cli-config.php

build: ## Build image used to install dependencies and run tests
	$(DC) build

install: ## Install dependencies with docker
	$(DC) run -T php composer install

test-integration: ## run integration tests (tests/integration)
	$(DC) run -T php-amqp-php-lib sh -c "dockerize -wait tcp://rabbitmq:5672 && vendor/bin/phpunit --config=phpunit-integration.xml"
	$(DC) run -T php-pecl-ext sh -c "dockerize -wait tcp://rabbitmq:5672 && vendor/bin/phpunit --config=phpunit-integration.xml"

test-unit: ## run unit tests (tests/unit)
	$(DC) run -T php vendor/bin/phpunit --config=phpunit.xml

test-validation: ## run validation tests (tests/validation)
	$(DC) run -T php sh -c "dockerize -wait tcp://rabbitmq:5672 && vendor/bin/phpunit --config=phpunit-validation.xml"