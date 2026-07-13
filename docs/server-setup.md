# Production Server Setup & Deployment Guide

## System Requirements

### PHP
- **Minimum:** PHP 8.2
- **Recommended:** PHP 8.2 or 8.3

### Required PHP Extensions
```
bcmath
ctype
curl
fileinfo
json
mbstring
openssl
pdo
pdo_mysql
tokenizer
xml
zip
gd          (for image processing)
intl        (for locale/date formatting)
```

### Web Server
- Apache 2.4+ with `mod_rewrite` enabled, **or**
- Nginx with FastCGI (PHP-FPM)

### Database
- MySQL 8.0+ (or MariaDB 10.6+)

### Node / NPM (build-time only)
- Node.js 18+ (only required to compile frontend assets; not needed at runtime)

---

## Deployment Checklist

### 1. Upload Files
```bash
# Upload project files to public_html (or subdirectory)
# Ensure .env is NOT committed to git and is created manually on the server
```

### 2. Install PHP Dependencies
```bash
composer install --optimize-autoloader --no-dev
```

### 3. Environment Configuration
```bash
# Copy the example env and fill in production values
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set at minimum:
```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=127.0.0.1
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password

MAIL_MAILER=smtp       # or log for testing
QUEUE_CONNECTION=sync  # or database/redis if Supervisor is available
```

### 4. Storage Link
```bash
php artisan storage:link
```
> Required so uploaded files (logos, attachments) are publicly accessible.

### 5. Run Migrations
```bash
php artisan migrate --force
```
> `--force` is required in production (suppresses interactive prompt).  
> **Never** run `migrate:fresh`, `migrate:refresh`, or `db:wipe` on production.

### 6. Cache Optimization
Run all three — this is the standard production performance set:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

To clear caches (before re-caching after a deployment):
```bash
php artisan optimize:clear
```

### 7. File Permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Queue Configuration

**Default (no Supervisor required):**  
`QUEUE_CONNECTION=sync` in `.env` — jobs run inline, no worker needed.

**Optional (background queuing with Supervisor):**  
Set `QUEUE_CONNECTION=database` and create the jobs table:
```bash
php artisan queue:table
php artisan migrate --force
```

Supervisor config (`/etc/supervisor/conf.d/manadeb-worker.conf`):
```ini
[program:manadeb-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/USER/public_html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=1
redirect_stderr=true
stdout_logfile=/home/USER/logs/worker.log
stopwaitsecs=3600
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start manadeb-worker:*
```

---

## Scheduler (Cron) Configuration

### The Single Required Cron Entry

Add **exactly one** cron job to the server. No other cron jobs are ever needed:

```
* * * * * php /home/USER/public_html/artisan schedule:run >> /dev/null 2>&1
```

> Replace `/home/USER/public_html` with the actual path to the project root  
> (the directory containing `artisan`).

To edit crontab:
```bash
crontab -e
```

### What the Scheduler Runs Internally

The Laravel Scheduler dispatches all timed commands automatically from that single cron entry. No additional cron jobs should ever be created for individual commands.

| Command | Artisan Signature | Frequency | Purpose |
|---|---|---|---|
| `ExpireTicketGracePeriods` | `tickets:expire-grace-periods` | Every hour | Closes resolved tickets after grace period |
| `SendExpiryAlerts` | `notify:expiry-alerts` | Daily at 08:00 | Portal notifications for expiring delegate/vehicle documents |
| `PruneNotifications` | `notifications:prune` | Weekly (Sunday 00:00) | Deletes read notifications older than 90 days |

To verify the schedule is registered correctly:
```bash
php artisan schedule:list
```

Expected output includes all three entries with their next due times.

---

## Rollback Notes

### Code Rollback
```bash
# Re-upload previous version files
# Then re-run cache commands:
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Migration Rollback
```bash
# Roll back the most recent migration batch
php artisan migrate:rollback

# Roll back a specific number of steps
php artisan migrate:rollback --step=3
```

> Never use `migrate:fresh` or `migrate:refresh` on production — these DROP all tables.

---

## Post-Deployment Verification

```bash
# Confirm application responds
curl -I https://yourdomain.com

# Confirm scheduler is wired up
php artisan schedule:list

# Confirm routes are cached
php artisan route:list | head -5

# Confirm queue is running (if using database driver)
php artisan queue:work --once
```

---

## Security Checklist

- [ ] `APP_DEBUG=false` in production `.env`
- [ ] `.env` file not accessible via HTTP (verify `public/.htaccess` is in place)
- [ ] `storage/` directory not directly web-accessible
- [ ] HTTPS enforced (SSL certificate installed)
- [ ] Database user has only the permissions it needs (no `GRANT ALL` in production)
- [ ] `php artisan config:cache` run after any `.env` change

---

## Shared Hosting Notes (cPanel / XAMPP-style)

If deploying to shared hosting where the document root is `public_html`:

```
public_html/        ← point domain here (contents of Laravel's /public)
manadeb/            ← Laravel root (outside public_html)
  artisan
  app/
  ...
```

Update `public_html/index.php` bootstrap paths accordingly, or use a symlink strategy.

Cron entry for cPanel:
```
* * * * * /usr/local/bin/php /home/USER/manadeb/artisan schedule:run >> /dev/null 2>&1
```

> Use `which php` on the server to confirm the correct PHP binary path.
