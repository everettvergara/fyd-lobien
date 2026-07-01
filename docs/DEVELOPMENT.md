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

## Module System

Modules are registered in `config/modules.php`. The `ModuleServiceProvider` automatically loads:

- `Routes/admin.php` — admin routes (prefixed with `/admin`, named `admin.*`)
- `Routes/web.php` — public routes (if present)
- `Views/` — Blade views (namespace: `{module}::`)
- `Migrations/` — database migrations (target: `Database/Migrations/` per [MODULE_STANDARD.md](MODULE_STANDARD.md))

The **target** module layout and registration flow are defined in
[MODULE_STANDARD.md](MODULE_STANDARD.md). **Module Registry** and **Menu Registry**
(see [FRAMEWORK.md](FRAMEWORK.md)) will replace manual wiring during Framework
Stabilization.

### Creating a New Module

1. Create the folder structure under `app/Modules/{ModuleName}/` per [MODULE_STANDARD.md](MODULE_STANDARD.md)
2. Add the module name to `config/modules.php` `enabled` array
3. Add permissions to `PermissionsSeeder` if the module needs RBAC
4. Register policies in `AppServiceProvider` if needed *(legacy MVP; Module Registry will automate)*
5. Add sidebar links in `resources/views/admin/layouts/partials/sidebar.blade.php` *(legacy MVP; Menu Registry will automate)*

Place models per [ADR 001](ADR/001-model-placement.md): business models in the
module; shared kernel models in `app/Models/`.

## Framework Services

Business modules must use framework services instead of duplicating logic.
See [ARCHITECTURE.md](ARCHITECTURE.md) and [FRAMEWORK.md](FRAMEWORK.md).

| Service | Current state | Target |
|---------|---------------|--------|
| Settings | `Setting` model + Settings module | Settings Service |
| Media Library | Media module (basic) | Media Service + picker |
| Audit Logs | `ActivityLogger` | Audit Logs module |
| Module Registry | `config/modules.php` | Self-registration via `Module.php` |
| Menu Registry | Hardcoded sidebar | Registry-driven admin nav |
| Security | Auth/Users/Roles modules | Consolidated security domain per [SECURITY.md](SECURITY.md) |

## Authentication

- Session-based auth using Laravel's `web` guard
- User statuses: `pending_verification`, `active`, `inactive`, `suspended`, `locked`
- Email verification required before login
- Role-based permissions via `HasRoles` trait

See [SECURITY.md](SECURITY.md) for the full security domain.

### Permission Format

Permissions follow the pattern `{module}.{action}`:

```
pages.view
pages.create
pages.edit
pages.delete
pages.publish
```

Super Administrators bypass all permission checks.

## Content Management

### Publishing

Content models (Page, Post, Banner) use the `Publishable` trait:

```php
Page::published()->get(); // Only published, date-valid content
```

Draft and archived content is not visible on the public website.

### SEO

Content models using `HasSeo` trait support polymorphic SEO metadata:

```php
$page->saveSeo([
    'seo_title' => 'Page Title',
    'meta_description' => 'Description',
]);
```

### Page Sections

Pages support ordered content sections via the `page_sections` table. Each section has a `component_type` and JSON `settings`. Available types:

- `hero_banner`
- `feature_grid`
- `cta`
- `statistics`
- `contact`
- `faq`

## Public Website

Public controllers live in `app/Http/Controllers/Public/`. Routes are defined in `routes/web.php`.

Shared Inertia data (via `HandleInertiaRequests`):

- `app` — site name, tagline, contact, social links
- `navigation` — header and footer menus

### Public Routes

| Route | Controller |
|-------|------------|
| `/` | HomeController |
| `/blog` | PostController@index |
| `/blog/{slug}` | PostController@show |
| `/search` | SearchController |
| `/{slug}` | PageController@show |

## Database Seeding

Seed order in `DatabaseSeeder`:

1. `PermissionsSeeder` — all module permissions
2. `RolesSeeder` — default roles with permission assignments
3. `SettingsSeeder` — default settings
4. `AuthenticationSeeder` — super admin account
5. `DemoSeeder` — demo pages, posts, banners, menus, users

```bash
php artisan db:seed                  # Seed without migrating
php artisan migrate:fresh --seed     # Fresh database with all data
php artisan db:seed --class=DemoSeeder  # Demo data only
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
| `DemoSeederTest` | Demo data seeding and accessibility |

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

## Current Phase

**Framework Stabilization** — See [ROADMAP.md](ROADMAP.md). Do not add new
business modules until framework services are stable.

## Future Modules (Post-Stabilization)

Products, Categories, E-Commerce, Events, Testimonials, Careers, Forms, AI Modules, and more.

Each future module must follow [MODULE_STANDARD.md](MODULE_STANDARD.md).
