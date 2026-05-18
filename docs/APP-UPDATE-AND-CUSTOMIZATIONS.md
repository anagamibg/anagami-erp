# App Update & Customizations Guide (без дизайна)

Този документ събира на едно място правилата за **обновяване на приложението** (upstream Anagami ERP update) и **къстомизациите**, които трябва да се запазят при всеки merge.

> **Обхват:** Тук е всичко **без фронтенд редизайна**. Редизайнът (SCSS override layer) се пази отделно — виж [redesign/UPGRADE-GUIDE.md](redesign/UPGRADE-GUIDE.md). Двете са независими и се пазят паралелно при ъпгрейд.

**Последна верификация спрямо кода:** 2026-07-01

---

## 1. Основни правила при app update

1. **Пази къстомизациите.** Преди merge на нова версия на Anagami ERP винаги свери списъка от Раздел 3 по-долу — всеки от тези файлове съдържа custom код, който upstream ще презапише.
2. **Не работи в `app2/` и `resources1/`.** Това бяха стари backup-и отпреди къстъм разработката. **Вече са премахнати** (не са и в git) — merge-ът с upstream, който ги ползваше като база, е приключил. Ако някога се появят пак, само за справка са — работи винаги в `app/` и `resources/`.
3. **Тествай локално преди deploy.** `npm run dev` → провери на http://127.0.0.1:8000 → чак тогава deploy.
4. **Git commit за всяка къстъм фийчър / bug fix.** Осмислени commit съобщения (виж Раздел 7), за да е проследимо какво е къстъм при следващия merge.
5. **Документирай.** Всяка нова къстомизация се добавя в този файл и в [CLAUDE.md](../CLAUDE.md), за да не се загуби при бъдещ ъпгрейд.
6. **Не пипай lang файлове извън `eng/`.** Само `resources/lang/eng/` е в git. `bg/` (основният UI език), `de/`, `ru/` и т.н. живеят само на сървъра — не ги merge-вай от upstream върху сървърните.

---

## 2. Процедура за upstream merge

```bash
# 1. Клон за ъпгрейда
git checkout main && git checkout -b upstream/worksuite-vX.Y

# 2. Разархивирай новата upstream версия отстрани и сравни срещу нашите app/ и resources/
#    За всеки файл от Раздел 3 → merge на ръка (нашите промени + upstream промените),
#    НЕ overwrite.

# 3. Останалите файлове (без къстъм) → може директно от upstream.

# 4. Rebuild
composer dump-autoload
npm run dev

# 5. Тествай локално: Dashboard, списък (DataTable), форма, detail/show, PDF, super-admin billing.

# 6. Merge в main + deploy (виж CLAUDE.md → Deploy Workflow)
```

Практично: държим списъка от Раздел 3 като checklist. Ако upstream пипне някой от тези файлове → ръчен merge. Ако не го е пипнал → нашата версия остава.

---

## 3. Legacy къстъм фийчъри — CUS_000326–329

Историческите къстомизации. Статусът е сверен спрямо текущия код на датата горе.

### CUS_000329 — Billing Package Limits (`max_clients`, `max_invoices`)  ✅ АКТИВЕН

Ограничава броя клиенти/фактури според billing пакета на компанията.

Ключови файлове:
- `app/Helper/start.php` — helper функции за проверка на лимити (`checkCompanyCanAddMoreClients()`, `checkCompanyCanAddMoreInvoices()`, `clearCompanyValidPackageCache()`)
- `app/Http/Middleware/CheckCompanyPackage.php`
- `app/Http/Controllers/InvoiceController.php`
- `app/Http/Controllers/ClientController.php`
- `app/Http/Controllers/SuperAdmin/BillingController.php`
- `app/Http/Controllers/SuperAdmin/PayFastController.php`
- `app/Jobs/ImportClientJob.php`
- `app/Http/Requests/SuperAdmin/Packages/StoreRequest.php`
- `app/Http/Requests/SuperAdmin/Packages/UpdateRequest.php`
- `app/DataTables/SuperAdmin/CompanyDataTable.php`
- `app/DataTables/SuperAdmin/PackageDataTable.php`
- `app/DataTables/InvoicesDataTable.php`
- `app/Observers/ClientDetailsObserver.php`
- `app/Observers/InvoiceObserver.php`
- `app/Observers/SuperAdmin/PackageObserver.php`
- `resources/views/super-admin/packages/ajax/create.blade.php`
- `resources/views/super-admin/packages/ajax/edit.blade.php`
- `resources/views/super-admin/billing/ajax/plan.blade.php`
- `resources/views/super-admin/companies/ajax/show.blade.php`
- `resources/views/super-admin/front/section/pricing-plan.blade.php`
- `resources/views/super-admin/saas/pricing-plan.blade.php`
- `resources/views/invoices/index.blade.php`
- `resources/views/invoices/ajax/show.blade.php`
- `resources/views/clients/index.blade.php`
- `resources/lang/eng/superadmin.php`

### CUS_000328 — Dual-language PDF (EN + BG)  ✅ АКТИВЕН (с известен бъг)

Всички `resources/views/*/pdf/*.blade.php` (~30 файла) имат guard `@if ($invoiceSetting->locale != 'bg')`, за да рендират EN + BG в един PDF.

> ⚠️ **Известен бъг (неоправен):** PDF-ът рендира 2× на български вместо EN + BG.

### CUS_000327 — Invoice Series  ⚠️ СТАТУС НЕЯСЕН

Не е открита ясна имплементация в diff-а. При работа по фактури — провери миграциите преди да приемеш, че липсва.

### CUS_000326 — Custom Fields за Finance Docs  ❌ НЕ Е АКТИВЕН В ТЕКУЩИЯ КОД

Оригиналната идея: добавяне на `CustomFieldsTrait` към `BankAccount`, `CreditNotes`, `Payment`, `Proposal`.

> **Верификация 2026-07-01:** тези 4 модела **вече не използват** `CustomFieldsTrait` (`app/Models/Proposal.php` например има само `HasCompany`). Trait-ът се ползва от други ~20 модела (Invoice, Estimate, Project, Task…), но **не** от четирите finance модела. Изглежда фийчърът е отменен или презаписан при по-ранен merge. **Не приемай, че съществува** без нова проверка на кода.

---

## 4. Актуални къстомизации (пази при update)

### 4.1 Custom code injection страници (super-admin)  ✅

Три под-страници в `/account/settings/` за инжектиране на код:

| Страница | Поле в `global_settings` | Инжектира се като |
|----------|--------------------------|-------------------|
| Custom CSS | `custom_css` | `<style>` в края на `<head>` (overrides всичко) |
| Custom JS | `custom_js` | `<script>` преди `</body>`, обвит в `try/catch` |
| Custom HEAD | `custom_head_html` | суров HTML в `<head>` (Crisp/Analytics/Pixel/meta) |

- Controllers: `app/Http/Controllers/Custom{Css,Js,Head}Controller.php`
- Views: `resources/views/settings/custom-{css,js,head}/index.blade.php` (CodeMirror редактор)
- Routes: `routes/web-settings.php` (`custom-{css,js,head}` resources)
- Инжекция: `resources/views/layouts/app.blade.php` (в `<head>` + преди `</body>`)

> 🔒 **Критично:** трите имена на полета (`custom_css`, `custom_js`, `custom_head_html`) са whitelisted в `config/xss_ignore.php`, за да не изтрива Froiden XSS middleware `<script>` таговете при запис. Ако добавиш ново поле с raw HTML/JS — добави го и там.

### 4.2 About страница  ✅

`/account/about` показва карта „Информация за приложението" (име + описание + URL + app/PHP/Laravel/DB версия + сървърно време).
- Controller: `app/Http/Controllers/SuperAdmin/AboutController.php`

### 4.3 Baked-in bug fixes  ✅

Оправени бъгове, вградени директно в кода — пази ги при merge:

- `app/Models/Expense.php` — `protected $with = ['currency', 'company:id,date_format']` (беше `company:id`). Без `date_format` гърми `Carbon::format(null)` в expense report под PHP 8.3 + strict Carbon.
- `app/Http/Controllers/FinanceReportController.php` — `$projectNames` се инициализира като празна колекция преди условния блок; иначе undefined variable когато няма съвпадащи плащания.
- `resources/js/bootstrap.js` — `window.Echo = new Echo(...)` guard-нато с `if (process.env.MIX_PUSHER_APP_KEY)`; иначе `new Pusher(undefined)` хвърля и каскадира в „init is not defined", заседнал preloader и т.н.
- `resources/views/layouts/app.blade.php` — `try { init() } catch(...)` + 5s `setTimeout` предпазна мрежа, която насила скрива preloader-а ако `$(window).on('load')` не се задейства.

---

## 5. Plugin модули (`Modules/`)

| Модул | Версия | Предназначение |
|-------|--------|----------------|
| `LanguagePack` | bundled | Управление на преводи |
| `Asset` | 2.1.22 | Фирмени активи (лаптопи, монитори, лицензи) + история на заемане + поддръжка |
| `Payroll` | 2.1.8 | Заплати, месечни фишове, payroll цикли, извънреден труд, разходи |

### Install pattern за нов/обновен модул

```bash
# 1. Разархивирай в Modules/<Name>/
# 2. Регистрирай в storage/app/modules_statuses.json
# 3. composer dump-autoload
# 4. php artisan module:migrate <Name> --force
# 5. php artisan module:seed <Name>
# 6. php artisan module:enable <Name>
```

На production, миграция след pull на нова версия на модул:
```bash
ssh backoffix-new 'cd /var/www/backoffix && sudo -u www-data php artisan module:migrate <Name> --force'
```

### Специфики

- **Payroll API е stub-нат.** `Modules/Payroll/Routes/api.php` е празен (оригиналът е запазен като `api.php.disabled`), защото `PayrollApiController` зависи от `Modules\RestAPI\…ApiBaseController`, който идва от RestAPI модула — а него не го инсталираме. **Web routes работят напълно.** При update на Payroll не връщай оригиналния `api.php`.
- Всеки `Modules/<X>/Resources/lang/*` (без `eng/`) е gitignored от собствения `.gitignore` на модула — преводите живеят на сървъра.
- **Забележка за `modules_statuses.json`:** към 2026-07-01 файлът съдържа само `{"LanguagePack": true}`, макар директориите `Asset/` и `Payroll/` да са налични. При проблем с активиране на модул — свери този файл.

---

## 6. Multi-tenancy при миграции/сийдъри

Всички tenant модели ползват `HasCompany` trait + глобален `CompanyScope`, който авто-филтрира по `company_id`. При миграция/сийдър, който трябва да види всички компании:

```php
Model::withoutGlobalScope(CompanyScope::class)->...
```

---

## 7. Git конвенции

- **НЕ добавяй `Co-Authored-By` trailer-и** към commit-ите (историята е чистена от тях).
- Solo repo — force-push на `main` е ОК при изрична заявка.
- BG преводите и demo данните живеят само на production сървъра, никога в git.

Commit съобщения за къстъм работа:
```bash
git commit -m "fix(invoices): guard null company address on show view"
git commit -m "feat(custom-css): inject global_settings.custom_css into head"
```

---

## 8. Свързани документи

- [CLAUDE.md](../CLAUDE.md) — пълен project overview, deploy workflow, server достъп
- [redesign/UPGRADE-GUIDE.md](redesign/UPGRADE-GUIDE.md) — как да се пази **дизайнът** при ъпгрейд (отделна тема)
- [app-vs-app2-resources-vs-resources1-differences.md](app-vs-app2-resources-vs-resources1-differences.md) — исторически diff анализ на премахнатите backup-и
