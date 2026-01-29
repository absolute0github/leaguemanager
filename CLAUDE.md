## CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## General Rules

1.  First think through the problem, read the codebase for relevant files. 
2.  Before you make any major changes, check in with me and I will verify the plan. 
3.  Please every step of the way just give me a high level explanation of what changes you made 
4.  Make every task and code change you do as simple as possible. We want to avoid making any massive or complex changes. Every change should impact as little code as possible. Everything is about simplicity. 
5.  Maintain a documentation file that describes how the architecture of the app works inside and out. 
6.  Never speculate about code you have not opened. If the user references a specific file, you MUST read the file before answering. Make sure to investigate and read relevant files BEFORE answering questions about the codebase. Never make any claims about code before investigating unless you are certain of the correct answer - give grounded and hallucination-free answers.

## Project Overview

IVL Travel Baseball League Management System - A PHP 8.3+ web application for managing travel baseball leagues with multi-role authentication, player management, team management, and a plugin-based module system.

## Technology Stack

*   **Backend**: PHP 8.2+, MySQL 8.0+
*   **Frontend**: Bootstrap 5, vanilla JavaScript
*   **Dependencies**: Composer (PHPMailer, phpdotenv, OTPHP, Bacon QR Code)
*   **Architecture**: Custom MVC with plugin-based module system

## Common Development Commands

### Setup and Installation

```plaintext
# Install dependencies
composer install

# Setup database and create superuser
php scripts/setup-database.php

# Import tryout data from CSV
php scripts/import-tryout-data.php

# Import commitment data from CSV
php scripts/import-commitment-data.php

# Reset all data (caution!)
php scripts/reset-data.php
```

### Database Operations

```plaintext
# Create database
mysql -u root -p -e "CREATE DATABASE leaguemanager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p leaguemanager &lt; database/schema.sql

# Check database connection
php scripts/check-database.php
```

### Development and Testing

```plaintext
# Test module system
php scripts/test-module-system.php

# Test admin panel workflows
php scripts/test-admin-workflows.php

# Setup test coach account
php scripts/setup-test-coach.php

# Verify CSV import
php scripts/verify-import.php

# Check email addresses in database
php scripts/check-emails.php
```

### Production Deployment

```plaintext
# Install production dependencies only
composer install --no-dev --optimize-autoloader

# Run deployment checks
php scripts/deploy.php check

# Setup deployment
php scripts/deploy.php setup

# Clear caches
php scripts/deploy.php clear

# Run migrations
php scripts/deploy.php migrate
```

## Core Architecture

### Application Lifecycle

**Entry Point** (`public_html/index.php`):

*   Loads Composer autoloader
*   Creates `App\Core\App` instance
*   Registers routes via callback
*   Calls `$app-&gt;run()` to dispatch

**App Initialization** (`app/Core/App.php`):

*   Loads `.env` file manually (no dotenv library in App.php)
*   Registers `ErrorHandler`
*   Initializes `Logger`, `Session`, `CSRF`
*   Creates `Router` instance
*   Initializes `ModuleManager` and loads enabled modules

**Request Handling**:

*   Router parses URL and matches routes
*   Dispatches to controller@method
*   Controllers inherit from `App\Core\Controller`
*   Controllers call Models and Services for business logic
*   Controllers render Views

### Routing System

Routes are defined in `public_html/index.php` using the Router's fluent API:

```php
$router-&gt;get('/admin/users', 'AdminController', 'listUsers');
$router-&gt;post('/admin/users/create', 'AdminController', 'createUser');
```

*   Supports GET, POST, PUT, DELETE
*   Supports route parameters: `/teams/{id}`
*   Controllers resolved from `App\Controllers\` namespace
*   Module controllers resolved from `App\Modules\{module}\Controllers\`

### Database Layer

**Singleton Pattern** - `App\Core\Database::getInstance()`

Key methods:

*   `execute($sql, $params)` - Execute prepared statement
*   `fetchOne($sql, $params)` - Fetch single row
*   `fetchAll($sql, $params)` - Fetch all rows
*   `insert($sql, $params)` - Insert and return last ID
*   `beginTransaction()`, `commit()`, `rollback()` - Transactions

All queries use prepared statements for SQL injection prevention.

### Module System Architecture

**Plugin-based extensibility** managed by `App\Modules\ModuleManager` (singleton).

#### Module Structure

```plaintext
app/Modules/{module-name}/
├── module.json          # Metadata, version, hooks
├── routes.php           # Route definitions
├── Controllers/         # Module controllers
├── Views/              # Module views
└── migrations/         # SQL migrations
```

#### module.json Format

```plaintext
{
    "name": "Module Name",
    "version": "1.0.0",
    "description": "Description",
    "author": "Author",
    "hooks": {
        "dashboard.coach": "ControllerName@methodName",
        "nav.coach.sidebar": "ControllerName@sidebarLink"
    },
    "routes": "routes.php"
}
```

#### Available Hooks

*   `dashboard.{role}` - Dashboard widgets (superuser, admin, coach, player)
*   `nav.{role}.sidebar` - Sidebar navigation items
*   `user.login`, `user.logout` - Authentication events
*   `player.created`, `player.approved` - Player lifecycle events

#### Module Lifecycle

1.  **Discovery** - `discoverModules()` scans `app/Modules/` for `module.json`
2.  **Installation** - `installModule()` adds to `modules` table, runs migrations
3.  **Enable** - `enableModule()` sets `enabled=1` in database
4.  **Load** - `loadEnabledModules()` called on app startup
5.  **Hook Registration** - Hooks from `module.json` registered automatically
6.  **Route Registration** - Routes from `routes.php` loaded after core routes

#### Executing Hooks

```php
$moduleManager = ModuleManager::getInstance();
$results = $moduleManager-&gt;executeHook('dashboard.coach', ['user' =&gt; $user]);
```

### Session and Authentication

*   Session managed by `App\Core\Session` singleton
*   CSRF protection via `App\Core\CSRF`
*   2FA via TOTP (`App\Models\TwoFactorAuth`, `App\Services\TwoFactorService`)
*   Password hashing: bcrypt with cost 12
*   Account lockout: 5 failed attempts = 15 min lockout
*   Roles: superuser, admin, coach, player

### Security Features

*   **CSRF**: Token on all forms, validated in POST handlers
*   **SQL**: Prepared statements throughout
*   **XSS**: Output escaping with `htmlspecialchars()` (use `e()` helper)
*   **Sessions**: HTTP-only, secure cookies (in production)
*   **Headers**: CSP, X-Frame-Options, X-XSS-Protection in `.htaccess`
*   **Passwords**: bcrypt with cost 12
*   **2FA**: Required for admin/superuser roles

### Logging System

**App\\Core\\Logger** - Singleton logger with multiple channels:

*   `app.log` - General application events
*   `error.log` - Errors only
*   `access.log` - HTTP requests (if `LOG_ACCESS=true`)
*   `audit.log` - User actions (login, data changes)

Logs auto-rotate at 10MB.

### View System

Views use plain PHP templates with layouts:

*   `app/Views/layouts/main.php` - Main layout
*   `app/Views/layouts/admin.php` - Admin panel layout
*   `app/Views/layouts/coach.php` - Coach portal layout

Controllers call `$this-&gt;view('path/to/view', $data)` to render.

## Key Design Patterns

1.  **Singleton**: Database, Session, Logger, ModuleManager
2.  **Front Controller**: All requests → `public_html/index.php` → Router
3.  **MVC**: Controllers handle requests, Models handle data, Views render
4.  **Service Layer**: Business logic in `app/Services/`
5.  **Hook System**: Event-driven module integration

## File Organization

*   `app/Controllers/` - Request handlers grouped by domain
*   `app/Models/` - Database models (User, Player, Team, etc.)
*   `app/Services/` - Business logic (EmailService, CoachService, etc.)
*   `app/Core/` - Framework components (Router, Database, Session, etc.)
*   `app/Modules/` - Plugin modules
*   `app/Views/` - Templates organized by role/domain
*   `database/` - Schema and migrations
*   `public_html/` - Web root (index.php, assets)
*   `scripts/` - CLI utilities
*   `storage/` - Logs, cache, uploads, temp files

## Important Conventions

### Controllers

*   Extend `App\Core\Controller`
*   Use `$this-&gt;requireAuth()` to enforce authentication
*   Use `$this-&gt;requireRole(['admin'])` to enforce role-based access
*   Use `$this-&gt;view($path, $data)` to render views
*   Use `$this-&gt;redirect($url)` to redirect
*   Use `$this-&gt;json($data)` for JSON responses

### Models

*   Extend `App\Core\Model`
*   Use `$this-&gt;db` to access Database singleton
*   Static methods for queries: `User::findById($id)`
*   Instance methods for object operations

### Views

*   Use `<!--?= e($variable) ?-->` to escape output
*   CSRF token: `<!--?= csrf_field() ?-->`
*   Session flash messages: `Session::has('success')`, `Session::get('success')`

### Module Routes

Module `routes.php` should return a closure:

```php
return function($router, $moduleName) {
    $router-&gt;get('/attendance', 'Modules\\attendance\\Controllers\\AttendanceController', 'index');
};
```

## Environment Configuration

Key `.env` variables:

*   `APP_ENV` - development/production
*   `APP_DEBUG` - true/false (disable in production)
*   `DB_*` - Database credentials
*   `MAIL_DRIVER` - smtp/mail/log
*   `SESSION_COOKIE_SECURE` - true for HTTPS
*   `LOG_ACCESS` - Log all requests (can be verbose)

## Production Deployment Notes

1.  Set `APP_ENV=production` and `APP_DEBUG=false`
2.  Set `SESSION_COOKIE_SECURE=true` (requires HTTPS)
3.  Configure SMTP credentials for email
4.  Run `composer install --no-dev --optimize-autoloader`
5.  Ensure `storage/` directories are writable
6.  Verify `.htaccess` is in `public_html/`
7.  Point web server DocumentRoot to `public_html/`

## Default Credentials

After running `php scripts/setup-database.php`:

*   Username: `superuser`
*   Password: `puppy-monkey-baby`

**Change immediately after first login!**