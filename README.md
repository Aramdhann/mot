# MOT — Money Tracker

Personal money tracker dashboard built with **Laravel 12** + **Filament v4** (panel, top navbar, MOT brand).

## Features

- **Wallets** — cash / bank / e-wallet, balances derived from transactions (never stored, can't drift)
- **Transactions** — income, expense, wallet-to-wallet transfer, loan payment (form adapts to type)
- **Budgets** — monthly limit per category, spent/remaining tracking with color states
- **Loans** — principal, auto-computed paid & remaining
- **Dashboard** — total balance / income / spending / debt stats, latest transactions, wallet & budget & loan widgets, spending-by-day chart (6-month history) + daily list
- Transfer to same wallet is blocked; every money flow is one `transactions` row

## Stack

| Piece | What |
|---|---|
| PHP 8.2+ | with `pdo_pgsql` |
| PostgreSQL | local or hosted |
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

### Database setup (PostgreSQL)

Ask your DBA or run as superuser once:

```sql
CREATE ROLE mot WITH LOGIN PASSWORD 'mot';
CREATE DATABASE mot OWNER mot;
-- separate DB for tests (never touches dev data):
CREATE DATABASE mot_testing OWNER mot;
```

`.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mot
DB_USERNAME=mot
DB_PASSWORD=mot
```

## Testing

28 tests / 95 assertions — CRUD through the real Filament UI, balance & loan & budget math,
page rendering, form validation, 404 page. Tests run against `mot_testing` and roll back.

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
cp .env.example .env && php artisan key:generate   # then edit DB_* to the server's PostgreSQL

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

Shared hosting works only if it offers **PostgreSQL + Composer + PHP 8.2+** (MySQL is NOT wired up —
the daily-spending queries use Postgres `extract()`; switching DBs means changing those to `DAY()`).

1. Upload the project (or git clone via terminal), keep it outside `public_html`
2. In cPanel → *Setup PHP App*: set docroot of your (sub)domain to the project's `public/`
3. Terminal: `composer install --no-dev --optimize-autoloader`
4. Create a PostgreSQL DB + user in cPanel, put the creds in `.env` (cPanel prefixes names, e.g. `cpaneluser_mot`)
5. Same finishing commands as VPS above (migrate, assets, caches, filament-user)

### Production checklist

- [ ] `.env`: `APP_ENV=production`, `APP_DEBUG=false` (debug pages leak paths/creds)
- [ ] `php artisan config:cache` after any `.env` change
- [ ] `php artisan test` green locally
- [ ] Admin password is strong (single-user app — every account has panel access)
- [ ] HTTPS in front of `/admin` (login credentials go over the wire)

## Project layout (the bits you'll touch)

```
app/Filament/Resources/     # CRUD screens: Wallets, Transactions, Budgets, Loans
app/Filament/Widgets/       # dashboard: stats, latest tx, wallet/budget/loan tables, daily chart+list
app/Models/                 # Transaction has the type-normalization hook; Wallet computes balances
app/Enums/TransactionType.php
app/Providers/Filament/AdminPanelProvider.php   # brand, colors, top navbar
database/migrations/
tests/Feature/              # one test file per feature
```
