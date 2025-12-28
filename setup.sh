#!/bin/bash

echo "========================================="
echo "XV Random Quotes - Development Setup"
echo "========================================="
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

echo "✓ Docker is installed"

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

echo "✓ Docker Compose is installed"
echo ""

# Build containers
echo "📦 Building Docker containers..."
docker-compose build

if [ $? -ne 0 ]; then
    echo "❌ Failed to build containers"
    exit 1
fi

echo "✓ Containers built successfully"
echo ""

# Install Composer dependencies
echo "📚 Installing Composer dependencies..."
docker-compose run --rm cli composer install

if [ $? -ne 0 ]; then
    echo "❌ Failed to install Composer dependencies"
    exit 1
fi

echo "✓ Composer dependencies installed"
echo ""

# Setup WordPress test environment
echo "🧪 Setting up WordPress test environment..."
echo "   This may take a few minutes on first run..."
docker-compose run --rm cli bash -c "chmod +x /plugin/bin/install-wp-tests-docker.sh && /plugin/bin/install-wp-tests-docker.sh wordpress_test manager secret testdb latest"

if [ $? -ne 0 ]; then
    echo "❌ Failed to setup WordPress test environment"
    echo "   You may need to run: make test-setup"
    exit 1
fi

echo "✓ WordPress test environment ready"
echo ""

# Verify the test library was installed
echo "🔍 Verifying WordPress test library..."
docker-compose run --rm cli bash -c "[ -f /tmp/wordpress-tests-lib/includes/functions.php ] && echo 'Test library found' || echo 'Test library missing'"

# Run a quick test to verify everything works
echo ""
echo "🧪 Running test suite to verify setup..."
docker-compose run --rm cli vendor/bin/phpunit

if [ $? -ne 0 ]; then
    echo ""
    echo "⚠️  Some tests may have failed, but the environment is set up."
    echo "   This is normal if you haven't written any implementation code yet."
    echo "   You can now start development with TDD!"
else
    echo ""
    echo "✓ All tests passed!"
fi

echo ""
echo "========================================="
echo "✓ Setup Complete!"
echo "========================================="
echo ""
echo "Available commands:"
echo "  make up         - Start WordPress (http://localhost:8080)"
echo "  make test       - Run tests"
echo "  make shell      - Open shell in CLI container"
echo "  make help       - Show all commands"
echo ""
echo "To start WordPress:"
echo "  make up"
echo ""
echo "Happy coding! 🚀"
