# XV Random Quotes v2.0 - Quick Start Guide

## 🚀 Getting Started (First Time Setup)

### Prerequisites
- Docker Desktop installed and running
- Make (optional, comes with macOS/Linux)

### One-Command Setup

```bash
./setup.sh
```

This will:
1. Build Docker containers
2. Install PHP dependencies (Composer)
3. Setup WordPress test environment
4. Run initial tests to verify everything works

### Manual Setup (if you prefer)

```bash
# 1. Build containers
make build

# 2. Install dependencies and setup tests
make install

# 3. Verify setup
make test
```

## 📋 Daily Development Workflow

### Running Tests (TDD)

```bash
# Run all tests
make test

# Run specific test file
docker-compose run --rm cli vendor/bin/phpunit tests/test-environment.php

# Run tests with coverage
make test-coverage
```

### Starting WordPress Development Environment

```bash
# Start WordPress
make up

# Visit http://localhost:8080 in your browser
```

### Working in the Container

```bash
# Open a shell
make shell

# Now you can run:
# - PHPUnit: vendor/bin/phpunit
# - Composer: composer require package-name
# - WP-CLI: wp plugin list
```

### Code Quality

```bash
# Check WordPress coding standards
make phpcs

# Auto-fix coding standard issues
make phpcbf
```

## 🧪 Test-Driven Development Workflow

Following TDD for the v2.0 refactor:

1. **Write a failing test first**
   ```bash
   # Create/edit test file in tests/
   # Example: tests/test-cpt-registration.php
   ```

2. **Run the test (it should fail)**
   ```bash
   make test
   ```

3. **Implement the feature**
   ```bash
   # Create/edit implementation in src/
   # Example: src/PostTypes/QuotePostType.php
   ```

4. **Run tests again (they should pass)**
   ```bash
   make test
   ```

5. **Refactor if needed**
   - Improve code quality
   - Tests should still pass

6. **Commit your changes**
   ```bash
   git add .
   git commit -m "Add feature X with tests"
   ```

## 📁 Project Structure

```
xv-random-quotes/
├── src/                    # New v2.0 code (PSR-4 autoloaded)
│   ├── PostTypes/         # Custom Post Type definitions
│   ├── Taxonomies/        # Taxonomy definitions
│   ├── Migration/         # Migration logic
│   ├── Admin/             # Admin UI components
│   ├── Blocks/            # Gutenberg blocks (PHP side)
│   └── Queries/           # WP_Query helpers
├── tests/                 # PHPUnit tests
│   ├── test-environment.php
│   ├── test-cpt-registration.php (to be created)
│   └── bootstrap.php
├── inc/                   # Legacy v1.x code (to be refactored)
├── vendor/                # Composer dependencies
├── docker-compose.yml     # Docker services
├── Makefile              # Convenient commands
└── phpunit.xml           # PHPUnit configuration
```

## 🐛 Troubleshooting

### Tests fail with "Cannot connect to database"

```bash
# Restart containers
make down
make up

# Re-setup test environment
make test-setup
```

### "Permission denied" errors

```bash
# Fix file ownership (run on host)
sudo chown -R $USER:$USER .
```

### Complete reset

```bash
# Nuclear option - removes everything and starts fresh
make clean
./setup.sh
```

### Can't access WordPress at localhost:8080

```bash
# Check if containers are running
docker-compose ps

# Start them if they're not
make up

# Check logs for errors
make logs
```

## 🔥 Useful Make Commands

```bash
make help          # Show all available commands
make build         # Build Docker containers
make up            # Start WordPress
make down          # Stop containers
make shell         # Open bash in CLI container
make test          # Run PHPUnit tests
make test-coverage # Run tests with coverage report
make phpcs         # Check coding standards
make phpcbf        # Fix coding standards
make logs          # Show container logs
make clean         # Remove everything
```

## 📊 Monitoring Test Progress

As you work through the v2.0 tasks:

```bash
# Run tests and see what passes
make test

# Generate coverage report to see what's tested
make test-coverage
# Open coverage/index.html in browser
```

## 🎯 Next Steps

Follow the TODO list in order:

1. ✅ **Task 1: Setup Development Environment** (DONE!)
2. 🔜 **Task 2: Write Tests for CPT Registration**
3. 🔜 **Task 3: Implement Custom Post Type Registration**
4. ... and so on

Each task follows TDD:
- Write test first
- Run test (fails)
- Implement feature
- Run test (passes)
- Refactor
- Commit

## 💡 Pro Tips

- Keep tests running in a separate terminal window
- Write descriptive test names that explain what should happen
- One assertion per test when possible
- Commit after each green test
- Don't skip writing tests (that's the whole point of TDD!)

Happy coding! 🎉
