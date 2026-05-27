# Anagami ERP

**Anagami ERP** е вътрешна система за управление на бизнеса на Anagami – многофирмена (multi‑tenant) платформа за управление на проекти, задачи, клиенти, фактуриране, човешки ресурси и поддръжка (тикети). Изградена е върху Laravel и е предназначена изцяло за вътрешно ползване в компанията.

> ⚠️ Хранилището е **частно**. Съдържа вътрешна конфигурация и не трябва да се публикува извън организацията.

## Основни възможности

| Област | Какво включва |
|---|---|
| **Проекти** | Проекти, етапи, Gantt диаграма, файлове, бележки, дискусии, време и разходи |
| **Задачи и тикети** | Задачи, подзадачи, Kanban табло, зависимости, тикети за поддръжка, канали и типове |
| **Клиенти и CRM** | Клиенти и контакти, документи, бележки, потенциални клиенти (leads), сделки, оферти |
| **Фактуриране** | Фактури, кредитни известия, оферти, плащания, повтарящи се фактури, банкови сметки, данъци и валути |
| **Човешки ресурси** | Служители, отдели, длъжности, присъствие, отпуски, смени, празници |
| **Отчети** | Финансови отчети, приходи срещу разходи, задачи, време, присъствие, отпуски, leads |
| **Модули** | `Asset` (активи на компанията), `Payroll` (заплати), `LanguagePack` (преводи) |
| **Известия** | Имейл, SMS (Twilio), Slack, Telegram, OneSignal |

## Технологичен стек

- **Backend:** PHP 8.3, Laravel 12
- **База данни:** MariaDB 11.8 (съвместима и с MySQL 8)
- **Frontend:** Blade, Bootstrap 4, SCSS, jQuery (без React/Vue)
- **Пакети:** nwidart/laravel-modules, Yajra DataTables, barryvdh/laravel-dompdf, maatwebsite/excel
- **Автентикация и права:** Laravel Fortify, Sanctum, Entrust (RBAC)
- **Реално време:** Pusher (стартира само при зададен `MIX_PUSHER_APP_KEY`)

## Бърз старт (локална среда)

```bash
# 1. Зависимости
composer install
npm install

# 2. Конфигурация
cp .env.example .env
php artisan key:generate

# 3. База данни – създайте празна база и настройте DB_* в .env
php artisan migrate --seed

# 4. Файлове и ресурси
php artisan storage:link
npm run dev

# 5. Стартиране
php artisan serve        # http://127.0.0.1:8000
```

Полезни команди:

```bash
npm run watch                       # прекомпилиране при промяна на SCSS/JS
npm run prod                        # продукционен билд
php artisan view:clear              # изчистване на компилираните изгледи
php artisan module:migrate <Име> --force   # миграции на модул (Asset, Payroll)
```

## Структура на проекта

```
app/            Модели, контролери, observers, събития, известия, DataTables
config/         Конфигурация на приложението
database/       Миграции, seeders, factories
Modules/        Вградени модули (Asset, Payroll, LanguagePack)
public/         Публични ресурси
resources/      Blade изгледи, SCSS (resources/sass/redesign), езикови файлове
routes/         web.php, web-public.php, web-settings.php, SuperAdmin/
docs/           Документация (редизайн, обновяване и персонализации)
```

### Архитектурни бележки

- **Multi‑tenancy:** моделите използват `HasCompany` и глобален `CompanyScope`, който филтрира по `company_id`.
- **Observers → Events → Listeners → Notifications:** бизнес логиката е предимно в observers; контролерите са тънки.
- **Помощни функции:** `app/Helper/start.php` (настройки, права, лимити на пакетите).
- **Езици:** в хранилището е само `resources/lang/eng`; българският превод (`bg`) се съхранява само на сървъра.

## Документация

- [`docs/APP-UPDATE-AND-CUSTOMIZATIONS.md`](docs/APP-UPDATE-AND-CUSTOMIZATIONS.md) – обновяване и персонализации
- [`docs/redesign/`](docs/redesign) – дизайн система и документация на редизайна
- [`CLAUDE.md`](CLAUDE.md) – технически бележки за разработка и внедряване

## Вграден отворен код

Bootstrap 4, Moment.js, Bootstrap Select, Datepicker, Font Awesome, Bootstrap Icons, Dropify, SweetAlert2, Quill, Frappe Charts, Bootstrap MultiDatesPicker, Bootstrap Colorpicker, jQuery UI, Highlight.js, Chart.js, Image Picker, Cropper.js.

## Собственост

© Anagami. Само за вътрешно ползване.
