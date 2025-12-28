#!/bin/bash

echo "========================================="
echo "WordPress Test Environment Diagnostics"
echo "========================================="
echo ""

echo "Checking test database connection..."
docker-compose run --rm cli bash -c "mysql -h testdb -u manager -psecret -e 'SELECT 1' 2>/dev/null && echo '✓ Database connection OK' || echo '❌ Cannot connect to database'"

echo ""
echo "Checking if test library directory exists..."
docker-compose run --rm cli bash -c "[ -d /tmp/wordpress-tests-lib ] && echo '✓ Directory exists' || echo '❌ Directory missing'"

echo ""
echo "Checking if test library files exist..."
docker-compose run --rm cli bash -c "[ -f /tmp/wordpress-tests-lib/includes/functions.php ] && echo '✓ Test library installed' || echo '❌ Test library missing'"

echo ""
echo "Checking WordPress core..."
docker-compose run --rm cli bash -c "[ -d /var/www/html/wp-includes ] && echo '✓ WordPress core found' || echo '❌ WordPress core missing'"

echo ""
echo "Checking test config..."
docker-compose run --rm cli bash -c "[ -f /tmp/wordpress-tests-lib/wp-tests-config.php ] && echo '✓ Test config exists' || echo '❌ Test config missing'"

echo ""
echo "Environment variables:"
docker-compose run --rm cli bash -c "echo WP_TESTS_DIR=\$WP_TESTS_DIR && echo WP_CORE_DIR=\$WP_CORE_DIR && echo WP_DB_NAME=\$WP_DB_NAME && echo WP_DB_HOST=\$WP_DB_HOST"

echo ""
echo "========================================="
echo "To reinstall the test environment, run:"
echo "  make test-setup"
echo "========================================="
