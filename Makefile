.PHONY: help build up down shell test test-setup composer install clean logs

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-20s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build Docker containers
	docker-compose build

up: ## Start WordPress development environment
	docker-compose up -d web

down: ## Stop all containers
	docker-compose down

shell: ## Open a shell in the CLI container
	docker-compose run --rm cli bash

composer: ## Install PHP dependencies via Composer
	docker-compose run --rm cli composer install

install: composer test-setup ## Complete installation (composer + test setup)

test-setup: ## Setup WordPress testing environment
	docker-compose run --rm cli bash -c "chmod +x /plugin/bin/install-wp-tests-docker.sh && /plugin/bin/install-wp-tests-docker.sh wordpress_test manager secret testdb latest"

test: ## Run PHPUnit tests
	docker-compose run --rm cli vendor/bin/phpunit

test-watch: ## Run tests in watch mode (requires entr)
	@echo "Watching for file changes... (Ctrl+C to stop)"
	@find tests src -name '*.php' | entr -c make test

test-coverage: ## Run tests with coverage report
	docker-compose run --rm cli vendor/bin/phpunit --coverage-html coverage

phpcs: ## Run PHP Code Sniffer
	docker-compose run --rm cli vendor/bin/phpcs --standard=WordPress src/ xv-random-quotes.php || true

phpcbf: ## Run PHP Code Beautifier and Fixer
	docker-compose run --rm cli vendor/bin/phpcbf --standard=WordPress src/ xv-random-quotes.php || true

logs: ## Show container logs
	docker-compose logs -f

clean: ## Remove all containers, volumes, and caches
	docker-compose down -v
	rm -rf vendor composer.lock coverage

reset: clean install up ## Complete reset and reinstall
