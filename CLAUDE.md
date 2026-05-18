# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Anagami ERP** is a SaaS business management platform built on Laravel 10. It provides multi-tenant project management, HR, invoicing, CRM, and support ticketing.

- **Production URL**: https://backoffix.com
- **Local project path**: `/Users/tsvet/Documents/Projects/backoffix.com`

## Tech Stack

- **Framework**: Laravel 10.0 on PHP **8.3** (composer.json requires `^8.2`)
- **Database**: **MariaDB 11.8** in production (was MySQL on SuperHosting)
- **Frontend**: Blade templates, Bootstrap 4, SCSS, jQuery (no React/Vue)
- **Key packages**: nwidart/laravel-modules, Yajra DataTables, barryvdh/laravel-dompdf, maatwebsite/excel
- **Auth**: Laravel Fortify + Sanctum + Entrust (RBAC)
- **Real-time**: Pusher (guarded in `bootstrap.js` — only initializes when `MIX_PUSHER_APP_KEY` is set)
- **Notifications**: Email, SMS (Twilio), Slack, Telegram, OneSignal

### Installed plugin modules (`Modules/`)

| Module | Version | Purpose |
|--------|---------|---------|
| `LanguagePack` | bundled | Translation management |
| `Asset` | 2.1.22 | Company assets (laptops, monitors, licenses) + lending history + maintenance |
| `Payroll` | 2.1.8 | Salary components/groups, monthly slips, payroll cycles, overtime, expenses |

Module install pattern: extract into `Modules/<Name>/`, register in `storage/app/modules_statuses.json`, `composer dump-autoload`, `php artisan module:migrate <Name> --force`, `module:seed`, `module:enable`. Each module's `Resources/lang/*` (except `eng/`) is gitignored by the module's own `.gitignore`.

Payroll-specific: `Modules/Payroll/Routes/api.php` is stubbed (original kept as `api.php.disabled`) because `PayrollApiController` depends on a `Modules\RestAPI\…ApiBaseController` class that the RestAPI module would provide — we don't install it. Web routes are fully functional.

## Local Development Environment

### Prerequisites

The local dev setup runs on macOS. Required software:
- **PHP 8.1+** — already installed
- **Node.js + npm** — already installed
- **MySQL 8.0** — install via `brew install mysql` or download from https://dev.mysql.com/downloads/
- **Composer** — install via `php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php composer-setup.php --install-dir=/usr/local/bin --filename=composer`

### First-time Local Setup

Run these commands in order from the project root (`/Users/tsvet/Documents/Projects/backoffix.com`):

```bash
# 1. Initialize git (if not already done)
git init
echo "node_modules/\nvendor/\n.env\nstorage/logs/\nstorage/framework/cache/\nstorage/framework/sessions/\nstorage/framework/views/\npublic/hot\npublic/storage\n*.log\n.DS_Store\napp2/\nresources1/" > .gitignore
git add -A
git commit -m "Initial commit — pre-redesign baseline"

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Configure environment
cp .env.example .env        # Or create .env manually (see below)
php artisan key:generate     # Only if APP_KEY is not set

# 5. Import database (see Database section below)

# 6. Create storage symlink
php artisan storage:link

# 7. Compile frontend assets
npm run dev

# 8. Start local server
php artisan serve            # Runs on http://127.0.0.1:8000
```

### Local .env Configuration

Create `.env` with these values (adjust DB credentials to match your local MySQL):

```env
APP_NAME=Anagami ERP
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=backoffix_local
DB_USERNAME=root
DB_PASSWORD=

# Keep these from production .env:
APP_KEY=<copy from production>
REDIRECT_HTTPS=false
APP_LOCALE=en
```

### Database Setup (from production dump)

```bash
# Production now runs on a self-managed Ubuntu 26.04 VPS — SSH alias backoffix-new
# (see "Production Server" section below). Streaming dump → local:
ssh backoffix-new 'sudo mariadb-dump backoffix_db | gzip' > ~/Downloads/backoffix_dump.sql.gz

# Import locally (MariaDB or MySQL 8 both work — schema is portable):
mysql -u root -e "CREATE DATABASE IF NOT EXISTS backoffix_local"
gunzip -c ~/Downloads/backoffix_dump.sql.gz | mysql -u root backoffix_local
```

> **Note**: The columns `companies.app_url` and `global_settings.global_app_url` do **not exist** in our schema (the original docs mention them but Anagami ERP has been customized). The site URL is controlled exclusively by `APP_URL` in `.env`. Set it to `http://127.0.0.1:8000` locally.

### Daily Dev Workflow

```bash
# Start dev server (terminal 1)
php artisan serve

# Watch & compile SCSS/JS on change (terminal 2)
npm run watch

# After SCSS changes without watch:
npm run dev
```

### Verifying Changes Locally

After any frontend change:
1. Run `npm run dev` (or have `npm run watch` running)
2. Open http://127.0.0.1:8000 in browser
3. Hard refresh (Cmd+Shift+R) to clear cached CSS/JS
4. Check both light and dark themes
5. Test on at least: Dashboard, a list page (DataTable), a form page, a detail/show page

## Production Server

Migrated **May 2026** from SuperHosting shared (`cloud-5e33dc.managed-vps.net`, LiteSpeed, MariaDB 10.11) to a self-managed VPS.

### SSH Access

```
Host : <виж SSH конфигурацията>
Port : <виж SSH конфигурацията>
User : <виж SSH конфигурацията>
Sudo : <в мениджъра на пароли – не се съхранява в хранилището>
Path : /var/www/backoffix
Alias: ssh backoffix-new   (configured in ~/.ssh/config, key auth)
```

### Server stack

| | |
|---|---|
| OS | Ubuntu 26.04 LTS |
| Web | Apache 2.4.66 + mod_php8.3 |
| PHP | **8.3.31** from `packages.sury.org` (Ubuntu 26.04 ships 8.5 which breaks `phpoffice/phpspreadsheet <8.5.0`) |
| DB | MariaDB 11.8.6 |
| SSL | Let's Encrypt (`certbot --apache`, auto-renew via systemd timer) |
| Cron | `* * * * * /usr/bin/php /var/www/backoffix/artisan schedule:run` as `www-data` |

### Important PHP config

`/etc/php/8.3/apache2/conf.d/99-backoffix.ini`:
```ini
upload_max_filesize = 100M
post_max_size = 100M
memory_limit = 512M
max_execution_time = 300
max_input_vars = 5000
date.timezone = Europe/Sofia
pcre.jit = 0   # MUST be 0; otherwise PCRE JIT allocation fails on Linux → 500 errors
```

### Apache vhost

- HTTP `/etc/apache2/sites-available/backoffix.conf` — redirects to HTTPS (added by certbot)
- HTTPS `/etc/apache2/sites-available/backoffix-le-ssl.conf` — serves `/var/www/backoffix/public`

### Permissions

- Code (`app/`, `resources/`, etc.): `ogi:www-data` 755/644
- `storage/`, `bootstrap/cache/`, `public/user-uploads/`: `www-data:www-data` 775/664
- `.env`: `ogi:www-data` 640

### Database credentials (production)

- DB: `backoffix_db`
- User: `backoffix_user`
- Password: в мениджъра на пароли (не се съхранява в хранилището)
- Root: socket-auth (`sudo mariadb`)

### Deploy Workflow

After testing locally, deploy via rsync:

```bash
# Compile assets
npm run prod

# Sync code (excluding caches/vendor/node_modules — composer/npm reinstall on server)
rsync -avz --exclude=vendor --exclude=node_modules --exclude=storage/logs \
  --exclude=storage/framework/cache --exclude=storage/framework/sessions \
  --exclude=storage/framework/views \
  ./ backoffix-new:/var/www/backoffix/

# On server: composer install (if vendor changed), rebuild caches
ssh backoffix-new 'cd /var/www/backoffix && sudo -u www-data php artisan view:clear && sudo -u www-data php artisan route:cache && sudo -u www-data php artisan config:cache'
```

For module migrations (after pulling new Asset/Payroll updates):
```bash
ssh backoffix-new 'cd /var/www/backoffix && sudo -u www-data php artisan module:migrate <Name> --force'
```

## Git Workflow

### Branch Strategy

```bash
main                    # Production-ready state (initial commit = pre-redesign)
├── redesign/phase-1    # Foundations (variables, typography, bootstrap overrides)
├── redesign/phase-2    # Layout (sidebar, header, content area)
├── redesign/phase-3    # Components (cards, buttons, tables, forms, etc.)
├── redesign/phase-4    # Page-specific styles
├── redesign/phase-5    # Dark theme
└── redesign/phase-6    # QA & polish
```

### Commit Conventions

```bash
git commit -m "redesign(sidebar): dark sidebar with section labels"
git commit -m "redesign(cards): white cards, rounded corners, soft shadows"
git commit -m "redesign(variables): new color palette, Onest font"
git commit -m "fix(sidebar): active state contrast in dark theme"
git commit -m "docs(changelog): update modified files list"
```

### Before Each Redesign Phase

```bash
git checkout main
git checkout -b redesign/phase-N
# ... make changes ...
git add -A
git commit -m "redesign(phase-N): description"
git checkout main
git merge redesign/phase-N
```

## Commands

```bash
# Local development
php artisan serve                    # Run dev server at http://127.0.0.1:8000
npm run dev                          # Compile frontend assets once
npm run watch                        # Watch & recompile on changes
npm run prod                         # Production build with minification

# Laravel
php artisan migrate                  # Run migrations
php artisan config:cache             # Cache config
php artisan view:clear               # Clear compiled views
php artisan cache:clear              # Clear application cache
php artisan queue:work               # Process queue jobs

# Git
git status                           # Check what changed
git diff                             # See exact changes
git stash                            # Temporarily save changes
git stash pop                        # Restore stashed changes
git log --oneline -20                # Recent commits

# Deploy
bash deploy.sh                       # Deploy to production (after local testing)
```

## Important: Duplicate Directories

- **`app2/`** — Old backup of `app/` BEFORE custom dev work. **Do NOT use or modify.**
- **`resources1/`** — Old backup of `resources/` BEFORE custom dev work. **Do NOT use or modify.**
- Always work with `app/` and `resources/` — they contain the current code.
- See `docs/app-vs-app2-resources-vs-resources1-differences.md` for full diff analysis.

## Architecture

### Multi-Tenancy

All tenant-aware models use `HasCompany` trait, which registers `CompanyScope` — a global query scope that auto-filters by `company_id = auth()->user()->company_id`. This means:
- Every query on tenant models is automatically scoped — no manual filtering needed
- When running migrations or seeders, use `->withoutGlobalScope(CompanyScope::class)` to bypass
- The `company()` helper retrieves current company from session; `user()` gets current auth user

### Request Lifecycle: Observer-Driven Architecture

Controllers are kept thin. Most business logic lives in **Observers** (163 total, registered in `EventServiceProvider`):
- Observers fire on model lifecycle hooks (`creating`, `created`, `updating`, `deleted`, etc.)
- Observers trigger **Events** (~95), which are handled by **Listeners** (~88)
- Listeners dispatch **Notifications** (~141) across multiple channels

**Flow**: Controller saves Model → Observer fires → Event dispatched → Listener runs → Notification sent

### Helper System (`app/Helper/start.php`)

Central 1500-line file with globally-available functions. Key patterns:
- **Settings accessors** (`global_setting()`, `invoice_setting()`, etc.) — cached in session or app cache
- **Permission checks** (`add_project_permission()`, `add_tasks_permission()`, etc.) — return permission objects
- **Package limit checks** (`checkCompanyCanAddMoreClients()`, `checkCompanyCanAddMoreInvoices()`, etc.) — use cache keys like `company_{id}_can_add_more_employees`
- Cache clearing: `clearCompanyValidPackageCache($companyId)` when limits change

### Route Organization

| File | Purpose | Key Middleware |
|------|---------|---------------|
| `routes/web.php` | Main authenticated routes (~135KB) | `web`, `check-company-package`, `auto-logout` |
| `routes/SuperAdmin/web.php` | Platform admin routes | `auth`, `super-admin` |
| `routes/web-public.php` | Unauthenticated routes (payment callbacks, public forms) | minimal |
| `routes/api.php` | API routes (minimal) | `api` |

### Frontend Build (`webpack.mix.js`)

Laravel Mix bundles jQuery + Bootstrap + plugins into `public/js/main.js` and SCSS into `public/css/main.css`. Rich text editing via Quill. No SPA framework — all server-rendered Blade with jQuery interactions.

## Custom Modifications

### Legacy custom features (CUS_000326–329) — preserve during upstream updates

1. **CUS_000329 — Billing Package Limits** (`max_clients`, `max_invoices`)
   - Key files: `app/Helper/start.php`, `CheckCompanyPackage` middleware, `InvoiceController`, `ClientController`, `BillingController`
2. **CUS_000328 — Dual-language PDF** (EN + BG invoice in same PDF)
   - Key files: All `resources/views/*/pdf/*.blade.php` templates
   - Known bug: PDF renders 2x Bulgarian instead of EN+BG
3. **CUS_000327 — Invoice Series** — Status unclear, no code diff found
4. **CUS_000326 — Custom Fields for Finance Docs** (Payment, CreditNotes, Proposal, BankAccount)
   - Key files: Models + Controllers + DataTables + Views for those 4 entities

### Newer additions

5. **Custom code injection pages** (super-admin settings) — 3 sub-pages at `/account/settings/`:
   - **Custom CSS** → `global_settings.custom_css`, injected as `<style>` at end of `<head>` (overrides everything)
   - **Custom JS** → `global_settings.custom_js`, injected as `<script>` before `</body>`, wrapped in `try/catch`
   - **Custom HEAD** → `global_settings.custom_head_html`, raw HTML injected in `<head>` (for Crisp/Analytics/Pixel/meta tags)
   - Controllers: `app/Http/Controllers/Custom{Css,Js,Head}Controller.php`
   - Views: `resources/views/settings/custom-{css,js,head}/index.blade.php` (CodeMirror editor)
   - Routes: `routes/web-settings.php` (`custom-{css,js,head}` resources)
   - Injection: `resources/views/layouts/app.blade.php` (in `<head>` + before `</body>`)
   - **Important**: These 3 field names are whitelisted in `config/xss_ignore.php` so Froiden's XSS middleware doesn't strip `<script>` tags when saving.

6. **About page** — `/account/about` shows merged "Информация за приложението" card (name + description + URL + app/PHP/Laravel/DB version + server time). Controller: `app/Http/Controllers/SuperAdmin/AboutController.php`.

7. **Bug fixes baked in** (search the commit log for context):
   - `app/Models/Expense.php` — `protected $with = ['currency', 'company:id,date_format']` (was `company:id`); without `date_format`, `Carbon::format(null)` fires in expense report under PHP 8.3 + strict Carbon
   - `app/Http/Controllers/FinanceReportController.php` — `$projectNames` initialized as empty collection before the conditional block; otherwise undefined variable when no payments match
   - `resources/js/bootstrap.js` — `window.Echo = new Echo(...)` guarded by `if (process.env.MIX_PUSHER_APP_KEY)`; without this, `new Pusher(undefined)` throws and cascades into `init is not defined`, stuck preloader, etc.
   - `resources/views/layouts/app.blade.php` — `try { init() } catch(...)` + 5s `setTimeout` safety net that force-hides the preloader if `$(window).on('load')` never fires

## Frontend Redesign

### Status: IN PROGRESS

See `docs/redesign/` for full documentation:
- `CHANGELOG.md` — All changes with dates
- `DESIGN-SYSTEM.md` — Colors, typography, spacing, component specs
- `MODIFIED-FILES.md` — Every file touched (critical for upstream merges)
- `UPGRADE-GUIDE.md` — How to preserve redesign during updates
- `ARCHITECTURE.md` — Redesign file structure and compilation

### Redesign Rules

1. **Override, don't modify** — Create new SCSS in `resources/sass/redesign/`, avoid editing original SCSS when possible
2. **Document everything** — Every change goes in `docs/redesign/CHANGELOG.md` and `MODIFIED-FILES.md`
3. **Blade changes are minimal** — Add CSS classes, don't restructure HTML
4. **Test locally first** — `npm run dev` → check http://127.0.0.1:8000 → then deploy
5. **Git commit per phase** — One branch per redesign phase, merge to main when verified
6. **Font**: Onest (Google Fonts) everywhere
7. **Reference design**: See `docs/redesign/demo.png` for target UI style

### Redesign File Structure

```
resources/sass/redesign/
├── _variables-redesign.scss      # Colors, spacing, shadows
├── _typography-redesign.scss     # Onest font, size scale
├── _bootstrap-overrides.scss     # Bootstrap 4 variable overrides
├── _mixins-redesign.scss         # Shared mixins
├── components/                   # Component overrides
│   ├── _sidebar.scss
│   ├── _header.scss
│   ├── _cards.scss
│   ├── _buttons.scss
│   ├── _tables.scss
│   ├── _forms.scss
│   ├── _modals.scss
│   ├── _alerts.scss
│   ├── _badges.scss
│   ├── _dropdowns.scss
│   ├── _pagination.scss
│   └── _tabs.scss
├── pages/                        # Page-specific styles
│   ├── _dashboard.scss
│   ├── _login.scss
│   ├── _invoices.scss
│   ├── _projects.scss
│   └── _settings.scss
├── _dark-theme-redesign.scss
└── redesign.scss                 # Main entry — imports everything
```

## Key Conventions

- Multi-tenancy via `company_id` on all tables; use `HasCompany` trait on models
- Custom fields via `CustomFieldsTrait` on models + `CustomFieldsRequestTrait` on form requests
- DataTables use Yajra server-side processing (76 DataTable classes in `app/DataTables/`)
- PDF generation via DomPDF with Blade templates in `resources/views/*/pdf/`
- Lang directories: only `eng/` is checked in (covers both regular and super-admin UI). `bg/` (Bulgarian — primary UI language for this install), `de/`, `ru/`, etc. are gitignored — they live only on the server. `resources/lang/*` is ignored except `eng/`, and each `Modules/<X>/Resources/lang/*` is also ignored except `eng/` via the module's own `.gitignore`. The `en/` directory doesn't exist — `APP_LOCALE` is set to `bg` and all users' `locale` column is `bg`.

## Git workflow notes

- **Do NOT add Co-Authored-By trailers** to commits (history was cleaned up to remove them).
- Solo repo, force-push on `main` is acceptable when explicitly requested.
- BG translations and demo data live only on the production server, never in git.
