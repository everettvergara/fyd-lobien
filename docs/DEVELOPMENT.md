# FYD CMS — Development Guide

> **Authoritative references:** See [docs/README.md](README.md) for the full
> documentation set. Architecture and framework rules in
> [ARCHITECTURE.md](ARCHITECTURE.md) and [FRAMEWORK.md](FRAMEWORK.md) supersede
> informal patterns described below where they conflict.

## Architecture Overview

FYD CMS is a single Laravel application with two separated portals:

| Portal | Stack | Route Prefix |
|--------|-------|--------------|
| Admin | Blade + Bootstrap 5 | `/admin` |
| Public | Vue 3 + Inertia.js + Bootstrap 5 | `/` |

Both portals share the same database and models. The Admin Portal manages content; the Public Website renders published content.

Styling: see [THEME.md](THEME.md) — **required reading for color, font, typography, and theme tasks** (includes Cursor agent rules).

## Module System

Modules are registered through the **Module Registry** ([FRAMEWORK.md](FRAMEWORK.md)).
The `ModuleServiceProvider` loads enabled modules from `config/modules.php` and
registers routes, views, and migrations automatically.

### Core modules

Listed in `config/modules.php` (target key: `core[]`). Always booted. Admin
menus appear in the **left sidebar panel** (Content, Administration, …).

Loaded assets per module:

- `Routes/admin.php` — admin routes (prefixed with `/admin`, named `admin.*`)
- `Routes/web.php` — public routes (if present)
- `Views/` — Blade views (namespace: `{module}::`)
- `Migrations/` or `Database/Migrations/` — see [MODULE_STANDARD.md](MODULE_STANDARD.md)

Policies, permissions, and menu items are declared in each module's `Module.php`.

### Installable business modules

Built under [`contrib/`](../contrib/), copied to `app/Modules/` for runtime, then
installed via **Administration → Modules**.

- One business module = one installable package = one **group** in the **right
  sidebar panel** (e.g. E-Commerce)
- Multiple **feature modules** (Products, Orders, …) = `menuItem()` entries
  under that group
- Install / disable / uninstall applies to the whole business package

See:

- [MODULE_LIFECYCLE.md](MODULE_LIFECYCLE.md) — site owner install procedure
- [MODULE_CONTRIBUTION.md](MODULE_CONTRIBUTION.md) — authoring in `contrib/`
- [contrib/README.md](../contrib/README.md)

### Creating a new core module

1. Create the folder structure under `app/Modules/{ModuleName}/` per
   [MODULE_STANDARD.md](MODULE_STANDARD.md)
2. Add the module name to `config/modules.php` `core[]` array
3. Declare policies, permissions, and menu items in `Module.php`

### Creating a new business module

1. Create the folder under `contrib/{ModuleName}/` per
   [MODULE_CONTRIBUTION.md](MODULE_CONTRIBUTION.md)
2. Set `isInstallable(): true` and `group` in `module.json`
3. Copy to `app/Modules/{ModuleName}/` and install from **Administration → Modules**

Do **not** add installable business modules to `config/modules.php`.

Place models per [ADR 001](ADR/001-model-placement.md): business models in the
module; shared kernel models in `app/Models/`.

## Framework Services

Business modules must use framework services instead of duplicating logic.
See [ARCHITECTURE.md](ARCHITECTURE.md) and [FRAMEWORK.md](FRAMEWORK.md).

| Service | Current state | Target |
|---------|---------------|--------|
| Settings | `Setting` model + Settings module | Settings Service |
| Media Library | DAM framework services + Media module UI | Media Service + reusable picker/components |
| Audit Logs | `ActivityLogger` | Audit Logs module |
| Module Registry | `config/modules.php` + `Module.php` | Module Manager for installable business modules |
| Menu Registry | Registry-driven admin nav | Dual-panel sidebar (core left, business right) |
| Security | Auth/Users/Roles modules | Consolidated security domain per [SECURITY.md](SECURITY.md) |

## Standard Admin Lists

Admin index/list screens shall use the Standard Admin List framework when
the screen represents a list of records.

Each applicable module list must define:

- No row number column
- ID primary key column
- Search fields (explicitly configured per module; see below)
- Clickable sortable columns
- Server-side pagination
- Bulk actions (where applicable)
- Permission-aware inline icon row actions

Filters are optional and shall be defined per module only when explicitly
requested. Do not add filter UI or filter methods unless a module spec
calls for them.

Search fields must be explicitly configured per module. Current modules:

| Module | Search fields | Placeholder |
|--------|---------------|-------------|
| Content | `title`, `slug` | Search title or slug... |
| Banners | `name`, `title` | Search name or title... |
| Menus | `name` | Search menu name... |
| Users | `name`, `email` | Search name or email... |
| Roles | `name`, `display_name` | Search role name... |
| Sessions | user `name`, `email` via `whereHas` | Search user name or email... |
| Audit Logs | `module`, `action`, user `name`/`email` via `whereHas` | Search module, action, or user... |

Row actions shall appear as compact icon buttons at the side of each row
with accessible labels. Do not use dropdown action menus for standard
admin lists unless the module documents a technical exception.

When creating a new module with an admin list, implement the list through
the framework configuration and module-specific service rather than
copying table, search, filter, sort, pagination, or action markup into
the module view. Every applicable admin list must display `No` as the
row number and `ID` as the record primary key.

## Authentication

- Session-based auth using Laravel's `web` guard
- User statuses: `pending_verification`, `active`, `inactive`, `suspended`, `locked`
- Email verification required before login
- Role-based permissions via `HasRoles` trait

See [SECURITY.md](SECURITY.md) for the full security domain.

### Permission Format

Permissions follow the pattern `{module}.{action}`:

```
content.view
content.create
content.edit
content.delete
content.publish
```

Super Administrators bypass all permission checks.

## Content Management

### Publishing

Content models (Content, Banner) use the `Publishable` trait:

```php
Content::published()->get(); // Only published, date-valid content
```

Draft and archived content is not visible on the public website.

### SEO

Content models using `HasSeo` trait support polymorphic SEO metadata:

```php
$content->saveSeo([
    'seo_title' => 'Page Title',
    'meta_description' => 'Description',
]);
```

See [CONTENT_MODULE.md](CONTENT_MODULE.md) for the unified content form, rich text body, and SEO accordion.

## Public Website

Public controllers live in `app/Http/Controllers/Public/`. Routes are defined in `routes/web.php`.

Shared Inertia data (via `HandleInertiaRequests`):

- `app` — site name, tagline, contact, social links
- `navigation` — header and footer menus

### Public Routes

| Route | Controller |
|-------|------------|
| `/` | HomeController |
| `/search` | SearchController |
| `/{slug}` | ContentController@show |

## Database Seeding

See [SEEDING.md](SEEDING.md) for the full inventory of tables, row counts, and customization guidance.

Seed order in `DatabaseSeeder`:

1. `PermissionsSeeder` — all module permissions
2. `RolesSeeder` — default roles with permission assignments
3. `SettingsSeeder` — default settings (`Your Website` branding)
4. `CacheSettingsSeeder` — cache settings
5. `BannerTemplateSeeder` — 12 system banner layout templates
6. `AddressSeeder` — Philippine provinces and cities
7. `AuthenticationSeeder` — super admin account
8. `SiteMaintenancePageSeeder` — maintenance page content
9. `SampleContentSeeder` — sample pages, articles, banners, and menus

```bash
php artisan db:seed                  # Seed without migrating
php artisan migrate:fresh --seed     # Fresh database with all data
php artisan db:seed --class=Database\\Seeders\\SampleContentSeeder  # Sample content only
```

## Testing

```bash
php artisan test                     # Run all tests
php artisan test --filter=CmsTest    # Run specific test class
```

Test suites:

| File | Coverage |
|------|----------|
| `AuthenticationTest` | Login, register, logout, access control |
| `AdministrationTest` | Users, roles, permissions, dashboard |
| `CmsTest` | CMS module CRUD |
| `PublicWebsiteTest` | Public page rendering, search, navigation |
| `SampleContentSeederTest` | Sample content seeding and accessibility |

Module-level `Tests/` folders are the target per [MODULE_STANDARD.md](MODULE_STANDARD.md).
Root `tests/Feature/` remains valid during the transition.

## Settings

Settings are stored in the `settings` table with `group`, `key`, `value` columns.

```php
Setting::get('contact', 'email');
Setting::set('general', 'website_name', 'My Site');
```

SMTP configuration is applied at boot via `MailConfigService`.

Configurable options should use Settings rather than hard-coded config where
possible (see [ARCHITECTURE.md](ARCHITECTURE.md)).

## Media Library

All uploads, media pickers, asset previews, metadata edits, folder
management, downloads, variants, and media deletion must use the Media
Library framework. See [MEDIA_LIBRARY.md](MEDIA_LIBRARY.md).

When a module stores a media foreign key, sync usage after create/update:

```php
app(\App\Services\Media\MediaUsageService::class)->syncModel($model, 'content', [
    'featured_image_id' => 'Featured Image',
]);
```

Remove usage when the owning model is deleted. This enables the Media
Library to warn before deleting assets in use.

## Banner Module

The Banner module is the standard reusable banner presentation engine for
homepage heroes, sliders, landing heroes, CTA banners, section banners,
sidebars, footers, and future popup/campaign banners.

Use `App\Modules\Banners\Services\BannerRenderingService` for public banner
payloads and `themes/fyd-default/js/Components/BannerRenderer.vue` for public display.
See [BANNER_MODULE.md](BANNER_MODULE.md).

## Code Conventions

- Follow Laravel conventions
- Keep modules self-contained
- Use Form Requests for validation
- Use Policies for authorization
- Log mutations via `ActivityLogger::log()`
- Use `ContentStatus` enum for publishable content
- Blade admin views extend `admin.layouts.app`
- Vue public pages use `PublicLayout`
- Prefer thin controllers and module `Services/` for business logic ([ARCHITECTURE.md](ARCHITECTURE.md))
- Bump the CMS template version in `config/cms.php` and [VERSION.md](VERSION.md) on every template change

## CMS Template Version

The template version (`major.minor.release`) is shown at the bottom of the admin sidebar.
See [VERSION.md](VERSION.md) for bump rules and the changelog.

```php
use App\Support\CmsVersion;

CmsVersion::string(); // e.g. "0.0.0"
```

## Current Phase

**Framework Stabilization** — See [ROADMAP.md](ROADMAP.md). Do not add new
business modules until framework services are stable.

## Future Modules (Post-Stabilization)

Products, Categories, E-Commerce, Events, Testimonials, Careers, Forms, AI Modules, and more.

Each future module must follow [MODULE_STANDARD.md](MODULE_STANDARD.md).
