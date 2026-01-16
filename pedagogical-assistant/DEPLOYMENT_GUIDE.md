# 🚀 Deployment & Development Guide

## Environment Setup

### Prerequisites
```bash
- PHP 8.1+
- Composer 2.x
- SQLite3
- Node.js 18+ (optional, for asset compilation)
- Git
```

### Verify Installation
```bash
php --version        # Should be 8.1+
composer --version   # Should be 2.x
sqlite3 --version    # Should be installed
```

---

## Local Development Setup

### 1. Clone & Install

```bash
# Navigate to project
cd pedagogical-assistant

# Install PHP dependencies
composer install

# Install Node dependencies (for assets)
npm install

# OR using Yarn
yarn install
```

### 2. Environment Configuration

```bash
# Copy environment template
cp .env .env.local

# Edit .env.local with local values
# Minimal required:
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
AI_API_PROVIDER=mock
```

### 3. Database Setup

```bash
# Create database
php bin/console doctrine:database:create

# Apply migrations
php bin/console doctrine:migrations:migrate

# Load test data
php test_db.php

# Verify schema
php bin/console doctrine:schema:validate
```

### 4. Build Assets (Optional)

```bash
# Development mode
npm run dev

# OR production build
npm run build
```

### 5. Clear Cache

```bash
php bin/console cache:clear --env=dev
php bin/console cache:warmup --env=dev
```

---

## Running the Development Server

### Option 1: PHP Built-in Server (Recommended)

```bash
# Start server on localhost:8000
php -S localhost:8000 -t public

# The application is now available at:
# http://localhost:8000
```

### Option 2: Symfony CLI

```bash
# Install Symfony CLI (if available)
# https://symfony.com/download

symfony serve

# Server runs on https://127.0.0.1:8000
```

### Option 3: Docker Compose

```bash
# Start containers
docker-compose up -d

# Access at http://localhost:8000

# Stop containers
docker-compose down
```

---

## Accessing the Application

Once the server is running:

| URL | Purpose |
|-----|---------|
| http://localhost:8000/ | Landing page |
| http://localhost:8000/login | Login form |
| http://localhost:8000/courses | Course management |
| http://localhost:8000/chat | Chat interface |
| http://localhost:8000/api/doc | Swagger UI documentation |
| http://localhost:8000/api-test | Interactive API testing |

---

## Database Management

### View Database Status

```bash
# Check pending migrations
php bin/console doctrine:migrations:status

# View database schema
php bin/console doctrine:schema:update --dump-sql

# Validate schema
php bin/console doctrine:schema:validate
```

### Reset Database

```bash
# Drop existing database
php bin/console doctrine:database:drop --force

# Create new database
php bin/console doctrine:database:create

# Apply migrations
php bin/console doctrine:migrations:migrate

# Load test data
php test_db.php
```

### Export Database

```bash
# SQLite backup
cp var/data.db var/data.backup.db

# Export SQL dump
sqlite3 var/data.db ".dump" > dump.sql
```

---

## Testing

### Run All Tests

```bash
# Execute PHPUnit
php bin/phpunit

# Run with coverage report
php bin/phpunit --coverage-html=coverage
```

### Run Specific Tests

```bash
# Auth controller tests only
php bin/phpunit tests/Controller/AuthControllerTest.php

# User entity tests
php bin/phpunit tests/Entity/UserTest.php

# Tests matching pattern
php bin/phpunit --filter testLogin
```

### Generate Coverage Report

```bash
php bin/phpunit --coverage-html=var/coverage
open var/coverage/index.html  # macOS
explorer var/coverage/index.html  # Windows
xdg-open var/coverage/index.html  # Linux
```

---

## API Testing

### Using the Web Dashboard

Visit http://localhost:8000/api-test in your browser for interactive testing.

### Using cURL

```bash
# Test authentication
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"prof@example.com","password":"password123"}' \
  -c cookies.txt

# Test protected endpoint (using session cookie)
curl -X GET http://localhost:8000/api/sessions \
  -b cookies.txt
```

### Using Postman

1. Import the API documentation from: http://localhost:8000/api/doc.jsonld
2. Create new requests for each endpoint
3. Set authentication headers as needed
4. Export collection for sharing

### Using VS Code REST Client

Create `test.http` file:

```http
### Login
POST http://localhost:8000/api/auth/login
Content-Type: application/json

{
  "email": "prof@example.com",
  "password": "password123"
}

### Get Sessions
GET http://localhost:8000/api/sessions
```

---

## Common Tasks

### Create New User

```bash
# Via API
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email":"newuser@example.com",
    "password":"securepass123"
  }'

# Via CLI (if needed)
php bin/console app:create-user newuser@example.com securepass123
```

### Generate Database Migration

```bash
# After entity changes
php bin/console make:migration

# Review migration file, then apply
php bin/console doctrine:migrations:migrate
```

### Check Routes

```bash
# List all routes
php bin/console debug:router

# Search for specific route
php bin/console debug:router | grep -i auth

# View route details
php bin/console debug:router dashboard_home
```

### Clear Logs

```bash
# Clear dev log
rm var/log/dev.log

# Or truncate
> var/log/dev.log
```

---

## Environment Variables

### Database

```env
# SQLite (Development)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# MySQL
DATABASE_URL="mysql://user:password@127.0.0.1:3306/pedagogical"

# PostgreSQL
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/pedagogical"
```

### AI Service

```env
# Provider options: mock, openai, mistral
AI_API_PROVIDER=mock

# OpenAI
AI_API_KEY=sk-...
AI_API_URL=https://api.openai.com/v1

# Mistral
AI_API_KEY=...
AI_API_URL=https://api.mistral.ai/v1

# Local LLM (Ollama)
AI_API_URL=http://localhost:11434/v1
```

### Application

```env
# Domain & Protocol
APP_ENV=dev                    # dev or prod
APP_DEBUG=1                    # 0 for production
APP_SECRET=random-secret

# CORS
CORS_ALLOW_ORIGIN=*           # * for dev, specific URLs for prod
```

---

## Troubleshooting

### 500 Error - Check Logs

```bash
# View latest errors
tail -f var/log/dev.log

# Or in Windows PowerShell
Get-Content var/log/dev.log -Tail 20 -Wait
```

### Database Connection Error

```bash
# Verify SQLite database exists
ls -la var/data.db

# Check permissions
chmod 755 var/
chmod 644 var/data.db

# Recreate if corrupted
rm var/data.db
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Session Issues (401 Unauthorized)

```bash
# Ensure var/sessions directory exists
mkdir -p var/sessions
chmod 755 var/sessions

# Clear sessions
rm -rf var/sessions/*
```

### Asset Loading Issues

```bash
# Rebuild assets
php bin/console asset-map:dump

# Or with Webpack Encore (if configured)
npm run dev
npm run watch
```

### Doctrine Cache Issues

```bash
# Clear all caches
php bin/console cache:clear

# Warmup cache
php bin/console cache:warmup

# Clear specific cache
php bin/console cache:pool:clear cache.app
```

---

## Production Deployment

### Pre-Production Checklist

```bash
# 1. Update environment to production
APP_ENV=prod
APP_DEBUG=0

# 2. Build assets
npm run build

# 3. Install dependencies (no dev)
composer install --no-dev --optimize-autoloader

# 4. Warmup cache
php bin/console cache:warmup --env=prod

# 5. Run tests
php bin/phpunit

# 6. Check security
composer audit

# 7. Database backup
sqlite3 var/data.db ".backup production_backup.db"
```

### Server Requirements (Production)

- PHP 8.1+ with extensions: sqlite3, json, intl, dom
- 512MB+ RAM
- 5GB+ disk space
- HTTPS certificate
- Automated backups

### Docker Deployment

```bash
# Build production image
docker build -t pedagogical-assistant:latest .

# Run container
docker run -d \
  -p 8000:8000 \
  -e APP_ENV=prod \
  -v data:/app/var \
  pedagogical-assistant:latest

# With docker-compose
docker-compose -f compose.yaml -f compose.prod.yaml up -d
```

### GitHub Actions CI/CD

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy

on:
  push:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - run: composer install
      - run: php bin/phpunit
      - run: composer audit
```

---

## Monitoring

### Application Health

```bash
# Check symfony health
curl http://localhost:8000/health

# View request logs (in tail)
tail -f var/log/dev.log | grep -i "error\|warning"
```

### Database Optimization

```bash
# Analyze tables
sqlite3 var/data.db "ANALYZE;"

# Vacuum database
sqlite3 var/data.db "VACUUM;"

# Get statistics
sqlite3 var/data.db ".stats on"
```

---

## Backup & Recovery

### Automated Backup

Create `scripts/backup.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/backups/pedagogical"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup database
cp var/data.db $BACKUP_DIR/data_$DATE.db

# Backup uploads
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz public/uploads/

# Keep last 7 days only
find $BACKUP_DIR -mtime +7 -delete
```

### Restore from Backup

```bash
# Restore database
cp backups/data_YYYYMMDD_HHMMSS.db var/data.db

# Restore uploads
tar -xzf backups/uploads_YYYYMMDD_HHMMSS.tar.gz

# Apply migrations if needed
php bin/console doctrine:migrations:migrate
```

---

## Performance Tips

### Caching

```bash
# Enable Redis caching (if available)
# In .env.local
REDIS_URL=redis://localhost:6379

# In config/packages/cache.yaml
framework:
  cache:
    default_redis_provider: '%env(REDIS_URL)%'
```

### Query Optimization

```bash
# Use Doctrine Query Builder for complex queries
# Avoid N+1 queries with eager loading
$repo->findBySessionWithExercises(); # With JOIN

# Check query count
php bin/console debug:profiler
```

### Database Indexing

```bash
# Add index for frequently queried fields
php bin/console make:migration
# Edit migration to add:
# $table->index(['user_id', 'created_at']);
php bin/console doctrine:migrations:migrate
```

---

## Security

### HTTPS/SSL Setup

```bash
# Generate self-signed certificate (dev only)
openssl req -new -newkey rsa:2048 -days 365 -nodes \
  -x509 -keyout server.key -out server.crt

# Use with PHP server
php -S localhost:8443 \
  -t public \
  -S server.crt:server.key  # Not supported by built-in server
```

### CORS Configuration

```env
# .env.local - For development
CORS_ALLOW_ORIGIN=*

# .env.prod - For production
CORS_ALLOW_ORIGIN=https://yourdomain.com
```

### API Key Rotation

```bash
# Generate new API keys
php bin/console security:generate-random-key

# Update .env with new key
AI_API_KEY=new_key_here

# Restart application
```

---

## Useful Commands

```bash
# Database
doctrine:database:create          # Create database
doctrine:database:drop            # Drop database
doctrine:migrations:migrate        # Apply migrations
doctrine:schema:validate           # Validate schema

# Cache
cache:clear                        # Clear all caches
cache:warmup                       # Warmup caches

# Development
debug:router                       # List all routes
debug:config                       # Show configuration
debug:container                    # Show services
debug:profiler                     # View profiler data

# Utilities
make:command                       # Create new command
make:controller                    # Create controller
make:entity                        # Create entity
make:migration                     # Create migration
make:form                          # Create form
```

---

## Support Resources

- **Symfony Docs**: https://symfony.com/doc/current/
- **API Platform**: https://api-platform.com/
- **Doctrine ORM**: https://www.doctrine-project.org/
- **PHP Manual**: https://www.php.net/manual/
- **Stack Overflow**: https://stackoverflow.com/questions/tagged/symfony

---

**Last Updated:** January 12, 2025  
**Maintained By:** Development Team
