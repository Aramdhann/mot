# MOT — Money Tracker

Personal money tracker dashboard built with **Laravel 12** + **Filament v4** (panel, top navbar, MOT brand).

## Features

- **Wallets** — cash / bank / e-wallet, balances derived from transactions (never stored, can't drift)
- **Transactions** — income, expense, wallet-to-wallet transfer, loan payment (form adapts to type)
- **Budgets** — monthly limit per category, spent/remaining tracking with color states
- **Loans** — principal, auto-computed paid & remaining
- **Dashboard** — total balance / income / spending / debt stats, latest transactions, wallet & budget & loan widgets, spending-by-day chart (6-month history) + daily list
- **Create account** — self-service registration at `/admin/register` (name/email/password), gated by `ALLOW_REGISTRATION`
- **Per-account data** — every wallet, transaction, budget and loan is scoped to its owner; accounts never see each other's data
- Transfer to same wallet is blocked; every money flow is one `transactions` row

## Stack

| Piece | What |
|---|---|
| PHP 8.2+ | with `pdo_mysql`
| MySQL 8+ | local or hosted (PostgreSQL also works — queries are compatible with both) |
| Composer | deps |
| Filament v4 | admin panel at `/admin` |
| No Vite/Node needed | unless you build a custom theme (`php artisan filament:theme`) |

## Local development

```bash
git clone <repo> mot && cd mot
composer install
cp .env.example .env && php artisan key:generate
# edit .env DB_* (see below)
php artisan migrate
php artisan filament:assets          # publishes panel CSS/JS (incl. chart.js)
php artisan make:filament-user       # name / email / password
php artisan serve                    # http://localhost:8000 → redirects to /admin
```

### Database setup (MySQL)

Ask your DBA or run as superuser once:

```sql
CREATE USER 'mot'@'localhost' IDENTIFIED BY 'mot';
CREATE DATABASE mot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- separate DB for tests (never touches dev data):
CREATE DATABASE mot_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON mot.* TO 'mot'@'localhost';
GRANT ALL PRIVILEGES ON mot_testing.* TO 'mot'@'localhost';
```

`.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mot
DB_USERNAME=mot
DB_PASSWORD=mot
```

## Testing

33 tests / 113 assertions — CRUD through the real Filament UI, balance & loan & budget math,
page rendering, form validation, registration (incl. disabled gate), per-user data isolation, 404 page.
Tests run against `mot_testing` and roll back.

```bash
php artisan test
```

Run these **before every deploy**. All green = safe to ship.

## Deploying to hosting

### A. VPS / cloud (recommended)

```bash
# on the server, once
git clone <repo> /var/www/mot && cd /var/www/mot
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate   # then edit DB_* to the server's MySQL

php artisan migrate --force
php artisan filament:assets
php artisan storage:link

# caches (speeds up production)
php artisan config:cache && php artisan route:cache && php artisan view:cache

php artisan make:filament-user                     # your real login
```

Point the webserver docroot to **`/var/www/mot/public`** (Nginx/Apache), PHP-FPM 8.2+.
Make `storage/` and `bootstrap/cache/` writable by the PHP user.

### B. Shared hosting (cPanel)

Shared hosting works if it offers **MySQL + Composer + PHP 8.2+** (MySQL is what nearly every
shared host ships, so this is the easy path — the app's queries run on both MySQL and PostgreSQL).

1. Upload the project (or git clone via terminal), keep it outside `public_html`
2. In cPanel → *Setup PHP App*: set docroot of your (sub)domain to the project's `public/`
3. Terminal: `composer install --no-dev --optimize-autoloader`
4. Create a MySQL DB + user in cPanel, put the creds in `.env` (cPanel prefixes names, e.g. `cpaneluser_mot`)
5. Same finishing commands as VPS above (migrate, assets, caches, filament-user)

### Production checklist

- [ ] `.env`: `APP_ENV=production`, `APP_DEBUG=false` (debug pages leak paths/creds)
- [ ] `ALLOW_REGISTRATION=false` after creating your account — every registered account gets full panel access (single-user app)
- [ ] `php artisan config:cache` after any `.env` change
- [ ] `php artisan test` green locally
- [ ] Admin password is strong (single-user app — every account has panel access)
- [ ] HTTPS in front of `/admin` (login credentials go over the wire)

## Project layout (the bits you'll touch)

```
app/Filament/Resources/     # CRUD screens: Wallets, Transactions, Budgets, Loans
app/Filament/Widgets/       # dashboard: stats, latest tx, wallet/budget/loan tables, daily chart+list
app/Filament/Auth/Register.php              # registration page (gated by ALLOW_REGISTRATION)
app/Models/Concerns/BelongsToUser.php    # per-user global scope + auto user_id (used by all 4 models)
app/Models/                 # Transaction has the type-normalization hook; Wallet computes balances
app/Enums/TransactionType.php
app/Providers/Filament/AdminPanelProvider.php   # brand, colors, top navbar, registration gate
database/migrations/
tests/Feature/              # one test file per feature
```
