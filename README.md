# IVL Travel Baseball League Management System

A comprehensive, mobile-friendly web application for managing travel baseball leagues built with PHP 8.3+, MySQL 8.0+, and Bootstrap 5.

## Features

### User Management
- Multi-role authentication (Superuser, Admin, Coach, Player)
- Two-Factor Authentication (TOTP) with Google Authenticator
- Email verification and password reset
- Account lockout after failed login attempts (5 attempts, 15 min lockout)

### Player Management
- Player registration with email lookup for existing players
- Auto-approval for players matching existing records
- Parent/guardian information tracking
- Digital waiver signing with signature capture
- Player profile management
- CSV data import from tryout and commitment forms

### Team Management
- Create and manage teams by age group
- Assign coaches (head, assistant, volunteer)
- Manage rosters with jersey numbers
- Team status tracking

### Coach Portal
- Dedicated dashboard for coaches
- View assigned teams and rosters
- Player and parent contact information
- Send team messages via email
- Export contacts to CSV

### Admin Panel
- User CRUD operations
- Player management with filtering
- Team management
- Pending registration approvals
- Module management (superuser only)

### Module System
- Plugin-based architecture for extensibility
- Hook system for dashboard widgets and navigation
- Example: Attendance Tracking module included
- Easy to create custom modules

## Requirements

- **PHP:** 8.1 or higher
- **MySQL:** 8.0 or higher
- **Web Server:** Apache with mod_rewrite or Nginx
- **Composer:** For PHP dependency management

### Required PHP Extensions
- pdo, pdo_mysql
- json, mbstring
- openssl, curl, gd (for QR codes)

## Quick Start

### 1. Install Dependencies
```bash
composer install
```

### 2. Configure Environment
```bash
cp .env.example .env
# Edit .env with your database credentials
```

### 3. Setup Database
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE leaguemanager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p leaguemanager < database/schema.sql

# Setup superuser
php scripts/setup-database.php
```

### 4. Verify Installation
```bash
php scripts/deploy.php check
```

### 5. Access Application
- URL: `http://your-domain/login`
- Username: `superuser`
- Password: `puppy-monkey-baby`

**Important:** Change the password and set up 2FA immediately!

## Directory Structure

```
leaguemanager/
├── app/
│   ├── Controllers/     # Request handlers
│   ├── Core/           # Framework (Router, Database, Session, etc.)
│   ├── Models/         # Database models
│   ├── Modules/        # Plugin modules
│   │   └── attendance/ # Example attendance module
│   ├── Services/       # Business logic
│   └── Views/
│       ├── admin/      # Admin panel views
│       ├── auth/       # Login, register, 2FA views
│       ├── coach/      # Coach portal views
│       ├── dashboard/  # Role-based dashboards
│       ├── emails/     # Email templates
│       ├── errors/     # Error pages (404, 500, 403)
│       └── layouts/    # Layout templates
├── database/
│   ├── migrations/
│   ├── seeds/
│   └── schema.sql
├── public_html/        # Document root
│   ├── assets/
│   ├── index.php
│   └── .htaccess
├── scripts/            # CLI tools
│   ├── deploy.php      # Deployment helper
│   ├── setup-database.php
│   └── import-tryout-data.php
├── storage/
│   ├── cache/
│   ├── logs/
│   ├── temp/
│   └── uploads/
├── .env.example
└── composer.json
```

## Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_ENV` | Environment (development/production) | development |
| `APP_DEBUG` | Show detailed errors | true |
| `DB_HOST` | Database host | localhost |
| `DB_DATABASE` | Database name | leaguemanager |
| `LOG_LEVEL` | Minimum log level | debug |
| `LOG_ACCESS` | Log HTTP requests | true |
| `SESSION_TIMEOUT` | Session timeout (seconds) | 7200 |
| `MAIL_DRIVER` | Email driver (smtp/mail/log) | mail |

See `.env.example` for complete list.

## Security Features

- **Authentication:** bcrypt password hashing (cost 12)
- **2FA:** TOTP with backup codes
- **CSRF:** Token protection on all forms
- **SQL:** Prepared statements everywhere
- **XSS:** Output escaping with htmlspecialchars
- **Sessions:** HTTP-only, secure cookies
- **Headers:** CSP, X-Frame-Options, X-XSS-Protection
- **Lockout:** 5 failed attempts = 15 min lockout

## CLI Scripts

| Command | Description |
|---------|-------------|
| `php scripts/deploy.php setup` | Initial setup |
| `php scripts/deploy.php check` | Pre-deployment checks |
| `php scripts/deploy.php clear` | Clear caches |
| `php scripts/deploy.php migrate` | Run migrations |
| `php scripts/setup-database.php` | Create tables and superuser |
| `php scripts/import-tryout-data.php` | Import CSV data |
| `php scripts/test-module-system.php` | Test modules |

## Module Development

Create modules in `app/Modules/your-module/`:

```
your-module/
├── module.json
├── routes.php
├── Controllers/
│   └── YourController.php
├── Views/
└── migrations/
```

**module.json:**
```json
{
    "name": "Your Module",
    "version": "1.0.0",
    "description": "Description here",
    "hooks": {
        "dashboard.coach": "YourController@widget",
        "nav.coach.sidebar": "YourController@sidebarLink"
    }
}
```

Available hooks:
- `dashboard.{role}` - Dashboard widgets
- `nav.{role}.sidebar` - Sidebar navigation
- `user.login`, `user.logout` - Auth events
- `player.created`, `player.approved` - Player events

## Logging

Logs are in `storage/logs/`:
- `app.log` - Application events
- `error.log` - Errors only
- `access.log` - HTTP requests
- `audit.log` - User actions

Logs auto-rotate at 10MB.

## Deployment Checklist

1. [ ] Set `APP_ENV=production`
2. [ ] Set `APP_DEBUG=false`
3. [ ] Set `SESSION_COOKIE_SECURE=true`
4. [ ] Configure SMTP credentials
5. [ ] Enable HTTPS
6. [ ] Uncomment HSTS header in .htaccess
7. [ ] Run `composer install --no-dev --optimize-autoloader`
8. [ ] Run `php scripts/deploy.php check`
9. [ ] Set proper file permissions
10. [ ] Configure backup procedures

## Troubleshooting

**404 errors:**
- Check mod_rewrite is enabled
- Verify .htaccess is present
- Check DocumentRoot points to public_html/

**Database connection failed:**
- Verify MySQL is running
- Check credentials in .env
- Ensure database exists

**Login issues:**
- Check account isn't locked (lockout_until in users table)
- Verify password hash is correct
- Check session storage is writable

**Email not sending:**
- Check MAIL_DRIVER setting
- Verify SMTP credentials for production
- Check storage/logs/app.log for errors

## Version History

- **v1.0** - Initial release
  - User authentication with 2FA
  - Player and team management
  - Coach portal
  - Admin dashboard
  - Module system with attendance tracking
  - Error handling and logging

## Support

- GitHub Issues: [repository-url]/issues
- Email: support@ivlbaseball.com

---

**Version:** 1.0.0
**Last Updated:** January 2026
**Status:** Production Ready
