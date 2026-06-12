# FarmConnect Kenya — Deployment Guide

## Pre-deployment checklist

- [ ] Copy `config/env.local.php.example` to `config/env.local.php`
- [ ] Set `APP_ENV=production`, correct `BASE_URL`, and database credentials
- [ ] Import `database/schema.sql` (or run all migrations in order)
- [ ] Run `php tools/seed_admin.php`
- [ ] Change default admin password after first login
- [ ] Ensure `uploads/` and subfolders are writable by the web server
- [ ] Confirm `uploads/.htaccess` blocks PHP execution
- [ ] Enable HTTPS on production host
- [ ] Set `display_errors=Off` in PHP production config

## Environment configuration

1. Copy the example file:

   ```bash
   cp config/env.local.php.example config/env.local.php
   ```

2. Edit `config/env.local.php`:

   ```php
   return [
       'APP_ENV'  => 'production',
       'BASE_URL' => 'https://yourdomain.com/',
       'DB_HOST'  => 'localhost',
       'DB_NAME'  => 'farmconnect_kenya',
       'DB_USER'  => 'your_db_user',
       'DB_PASS'  => 'your_secure_password',
   ];
   ```

3. **Do not commit** `config/env.local.php` (listed in `.gitignore`).

## Database migrations (existing installs)

Run in order if upgrading:

```bash
php tools/migrate_phase6_orders.php
php tools/migrate_phase6b_orders.php
php tools/migrate_phase7.php
```

Fresh install: import `database/schema.sql` only.

## Server requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- PDO MySQL extension
- GD extension (recommended — product image optimization)
- Apache with `mod_rewrite` optional; document root points to project folder

## Laragon (local)

- Project path: `C:\laragon\www\farmconnect`
- URL: `http://farmconnect.test/` or `http://localhost/farmconnect/`
- Update `BASE_URL` in `env.local.php` to match

## XAMPP (local)

- Project path: `C:\xampp\htdocs\farmconnect`
- URL: `http://localhost/farmconnect/`

## Production hardening

- Use strong database passwords and dedicated DB user (not `root`)
- Keep `config/env.local.php` outside web root if possible, or block direct access
- Review file upload limits in `php.ini` (`upload_max_filesize`, `post_max_size`)
- Regular MySQL backups
- Session cookies: ensure `session.cookie_httponly=1`; use `secure` flag over HTTPS

## Smoke test after deploy

1. Home page and marketplace load with filters/pagination
2. Customer can register, login, place order
3. Farmer receives notification and can accept order
4. Customer sees order status update and notification
5. Admin dashboard shows order stats and activity feed

## Support files

| File | Purpose |
|------|---------|
| `README.md` | Feature overview and setup |
| `INSTALLATION.md` | Step-by-step local install |
| `config/env.local.php.example` | Environment template |
| `database/schema.sql` | Full database schema |
