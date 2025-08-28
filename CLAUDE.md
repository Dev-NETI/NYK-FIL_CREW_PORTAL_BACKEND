# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 PHP backend application for a crew portal. It uses Laravel Breeze for authentication with API support via Laravel Sanctum. The project is configured to use SQLite as the default database.

## Development Commands

### Starting Development Environment

```bash
composer dev
```

This starts all development services concurrently:

-   PHP development server (`php artisan serve`)
-   Queue worker (`php artisan queue:listen --tries=1`)
-   Log monitoring (`php artisan pail --timeout=0`)
-   Vite development server (`npm run dev`)

### Individual Commands

```bash
# Start development server only
php artisan serve

# Run database migrations
php artisan migrate

# Refresh migrations with seeding
php artisan migrate:refresh --seed

# Clear application caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Testing

```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test suites
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run tests with coverage
php artisan test --coverage
```

### Code Quality

```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Run in check mode (no changes)
./vendor/bin/pint --test
```

## Architecture

### Authentication System

-   Uses Laravel Breeze with API authentication
-   Laravel Sanctum provides token-based API authentication
-   Authentication routes are in `routes/auth.php`
-   Controllers are in `app/Http/Controllers/Auth/`

### API Structure

-   Main API routes in `routes/api.php`
-   Protected routes use `auth:sanctum` middleware
-   Current user endpoint: `GET /api/user` (requires authentication)

### Database

-   Default: SQLite (`database/database.sqlite`)
-   Configured for MySQL/MariaDB support
-   Migrations in `database/migrations/`
-   Model factories in `database/factories/`
-   Seeders in `database/seeders/`

### Testing

-   PHPUnit configuration in `phpunit.xml`
-   Feature tests in `tests/Feature/`
-   Unit tests in `tests/Unit/`
-   Uses in-memory SQLite for testing
-   Authentication tests included for registration, login, password reset, email verification

### Key Configuration

-   Application uses SQLite by default
-   Queue connection set to database
-   Cache store set to database
-   Mail driver set to log for development
-   Environment variables in `.env.example`

### Project Structure

```
app/
├── Http/Controllers/     # Controllers (including Auth/)
├── Models/              # Eloquent models
└── Providers/           # Service providers

database/
├── factories/           # Model factories
├── migrations/         # Database migrations
└── seeders/            # Database seeders

routes/
├── api.php             # API routes
├── auth.php            # Authentication routes
├── console.php         # Console commands
└── web.php             # Web routes

tests/
├── Feature/            # Feature tests
└── Unit/              # Unit tests
```
