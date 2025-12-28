# Docker Usage Guide

This project uses **one** `docker-compose.yml` file that provides two environments:

## 🔧 Development Environment (Manual Testing)

**Services:** `web` + `db`

```bash
# Start WordPress site
docker-compose up -d

# View logs
docker-compose logs -f web

# Stop (keeps database)
docker-compose down

# Wipe database and start fresh
docker-compose down -v
docker-compose up -d
```

**Access:** http://localhost:8080

**Database:** Persistent (`data` volume)

**Use for:**
- Manual development and testing
- Adding quotes via WordPress admin
- Testing plugin UI
- Integration testing with real WordPress

---

## 🧪 Test Environment (Automated Tests)

**Services:** `cli` + `testdb`

```bash
# Run all tests
docker-compose run --rm cli vendor/bin/phpunit

# Run specific test file
docker-compose run --rm cli vendor/bin/phpunit tests/test-quote-migration.php

# Run with verbose output
docker-compose run --rm cli vendor/bin/phpunit --testdox

# Install/update dependencies
docker-compose run --rm cli composer install
```

**Database:** Ephemeral (`testdb` with tmpfs - resets on restart)

**Use for:**
- PHPUnit tests
- TDD workflow
- Continuous integration

---

## Key Points

### Isolation
- `web` uses `db` (persistent)
- `cli` uses `testdb` (ephemeral)
- They **never** interfere with each other

### The `wordpress` Volume
Used by `cli` to:
- Provide WordPress core files for test framework
- Cache core files to speed up test runs

The `web` container gets fresh WordPress core files from the `wordpress:6` image on each start.

### Workflows

**Start fresh for integration testing:**
```bash
docker-compose down -v  # Wipe all data
docker-compose up -d    # Fresh WordPress
# Visit http://localhost:8080, complete setup
# Activate plugin → test migration
```

**Daily development:**
```bash
docker-compose up -d    # Keep existing data
# Work, test, develop
docker-compose down     # Stop, keep data
```

**Run tests while developing:**
```bash
# Terminal 1: Development environment
docker-compose up -d

# Terminal 2: Run tests (uses testdb, doesn't affect dev db)
docker-compose run --rm cli vendor/bin/phpunit
```

---

## Volume Management

List volumes:
```bash
docker volume ls | grep xv-random-quotes
```

Remove all volumes (fresh start):
```bash
docker-compose down -v
```

Remove just the database:
```bash
docker volume rm xv-random-quotes_data
```
