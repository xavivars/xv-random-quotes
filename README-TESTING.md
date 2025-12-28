# Testing Environment Guide

This project uses a fully dockerized development and testing environment. You don't need PHP, Composer, or WordPress installed on your host machine.

## Prerequisites

- Docker
- Docker Compose
- Make (optional, but recommended)

## Quick Start

### 1. Initial Setup

```bash
# Build the Docker containers
make build

# Install dependencies and setup test environment
make install

# Start WordPress development server
make up
```

Your WordPress site will be available at: http://localhost:8080

### 2. Running Tests

```bash
# Run all tests
make test

# Run tests with coverage report
make test-coverage

# View coverage report (opens coverage/index.html)
```

### 3. Development Workflow

```bash
# Open a shell in the CLI container to run commands
make shell

# Inside the shell, you can:
# - Run specific test files: vendor/bin/phpunit tests/test-specific.php
# - Run composer commands: composer require package-name
# - Run wp-cli commands: wp plugin list
```

## Available Make Commands

- `make help` - Show all available commands
- `make build` - Build Docker containers
- `make up` - Start WordPress development environment
- `make down` - Stop all containers
- `make shell` - Open bash shell in CLI container
- `make composer` - Install/update PHP dependencies
- `make install` - Complete installation (composer + test setup)
- `make test-setup` - Setup WordPress testing environment
- `make test` - Run PHPUnit tests
- `make test-coverage` - Run tests with HTML coverage report
- `make phpcs` - Run PHP Code Sniffer (check coding standards)
- `make phpcbf` - Run PHP Code Beautifier (fix coding standards)
- `make logs` - Show container logs
- `make clean` - Remove all containers and volumes
- `make reset` - Complete reset and reinstall

## Manual Commands (without Make)

If you prefer not to use Make:

```bash
# Build containers
docker-compose build

# Install dependencies
docker-compose run --rm cli composer install

# Setup test environment
docker-compose run --rm cli bash -c "chmod +x /plugin/bin/install-wp-tests-docker.sh && /plugin/bin/install-wp-tests-docker.sh wordpress_test manager secret testdb latest"

# Run tests
docker-compose run --rm cli vendor/bin/phpunit

# Start WordPress
docker-compose up -d web
```

## Docker Services

- **web** - WordPress 6.x with Apache (port 8080)
- **db** - MariaDB 10 (development database)
- **testdb** - MariaDB 10 (test database, uses tmpfs for speed)
- **cli** - PHP 7.4 CLI with Composer, WP-CLI, PHPUnit

## Testing Framework

The testing environment includes:

- PHPUnit 9.5 (compatible with PHP 7.4+)
- WordPress Test Suite (latest version)
- Yoast PHPUnit Polyfills (for PHP 8+ compatibility)
- WordPress Coding Standards (WPCS)
- PHP_CodeSniffer

## Writing Tests

Tests are located in the `tests/` directory and follow the WordPress plugin testing conventions:

```php
<?php
namespace XVRandomQuotes\Tests;

use WP_UnitTestCase;

class MyTest extends WP_UnitTestCase {
    public function test_something() {
        $this->assertTrue(true);
    }
}
```

## Troubleshooting

### Database connection issues

If you encounter database connection errors:

```bash
# Restart all containers
make down
make up

# Re-run test setup
make test-setup
```

### Permission issues

If you have permission issues with files created by Docker:

```bash
# Fix ownership (run on host)
sudo chown -R $USER:$USER .
```

### Clean slate

If things are really broken:

```bash
# Complete reset
make clean
make install
make up
```

## TDD Workflow

Following Test-Driven Development:

1. Write a failing test in `tests/`
2. Run tests: `make test`
3. Implement the feature in `src/`
4. Run tests again until they pass
5. Refactor if needed
6. Repeat

## CI/CD Integration

The test suite is designed to run in CI/CD environments. The same Docker setup can be used in:

- GitHub Actions
- GitLab CI
- Travis CI
- CircleCI

Example GitHub Actions workflow is provided in `.github/workflows/test.yml` (to be created).
