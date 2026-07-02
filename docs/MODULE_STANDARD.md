# FYD CMS Module Standard

Every module shall follow the same structure.

    Module/
     ├── Controllers
     ├── Models
     ├── Requests
     ├── Policies
     ├── Services
     ├── Routes
     ├── Views
     ├── Database
     │    ├── Migrations
     │    └── Seeders
     ├── Tests
     └── Module.php

Requirements

-   CRUD where applicable
-   Form Requests for validation
-   Laravel Policies
-   Feature Tests
-   Registration through Module Registry
-   Registration through Menu Registry
-   Activity logging
-   Media references registered through the Media Library usage tracker
-   Standard Admin List definition for every applicable admin index/list
    screen

## Admin Lists

Modules with admin index/list screens shall use the Standard Admin List
framework instead of module-specific table implementations.

Required list capabilities where applicable:

-   `No` row number column
-   `ID` primary key column
-   Searcher (fields explicitly configured per module)
-   Clickable sortable columns
-   Server-side pagination
-   Bulk actions (where applicable)
-   Permission-aware inline icon row actions

Filters are optional and shall be defined per module only when explicitly
requested.

Search fields must be explicitly configured per module:

| Module | Search fields | Placeholder |
|--------|---------------|-------------|
| Content | `title`, `slug` | Search title or slug... |
| Banners | `name`, `title` | Search name or title... |
| Menus | `name` | Search menu name... |
| Users | `name`, `email` | Search name or email... |
| Roles | `name`, `display_name` | Search role name... |
| Sessions | user `name`, `email` via `whereHas` | Search user name or email... |
| Audit Logs | `module`, `action`, user `name`/`email` via `whereHas` | Search module, action, or user... |

Row actions shall be rendered as inline icon buttons on the side of each
row. Dropdown action menus are not part of the standard list pattern
unless the module documents a technical exception.

When creating a new module, define its list columns, search fields,
sortable columns, row actions, bulk actions, and pagination
through the Standard Admin List framework. Every applicable admin list
must display `No` as the row number and `ID` as the record primary key.

The Banners module is the reference implementation for rich marketing
content lists that need preview columns, module-specific filters, and bulk
actions with additional input such as status changes or archive.

## MVP Compatibility

Existing modules may use flat `Migrations/` and `Seeders/` folders at the
module root. New modules should use the `Database/` nesting shown above.
During Framework Stabilization, existing modules will migrate incrementally
without breaking changes.

See [ADR 001](ADR/001-model-placement.md) for model location rules.

## Core modules vs installable business modules

FYD CMS has two module tiers:

| Tier | Example | Location | Enabled via |
|------|---------|----------|-------------|
| **Core** | Content, Users, Settings | `app/Modules/` | `config/modules.php` `core[]` |
| **Business** | Commerce, Careers, DemoNotes | `contrib/` → `app/Modules/` | **Administration → Modules** (install) |

Core modules use the layout above. Legacy flat `Migrations/` at module root is
allowed for existing core modules during Framework Stabilization.

### Installable business module layout

New **installable** business modules must follow the strict layout:

- Source in [`contrib/{ModuleName}/`](../contrib/)
- Runtime copy in `app/Modules/{ModuleName}/`
- `module.json` + `Module.php` with `isInstallable(): true`
- `Database/Migrations/` and `Database/Seeders/` only (no legacy flat paths)
- Every migration must implement `down()` for uninstall
- **`group`** in `module.json` — right-sidebar business group (e.g. E-Commerce)
- **`features[]`** — menu items under that group (Order Processing, Products, …)
- One `menuItem()` per feature; do not set custom `section` on installable modules

Full specification:

- [MODULE_CONTRIBUTION.md](MODULE_CONTRIBUTION.md) — authoring in `contrib/`
- [MODULE_LIFECYCLE.md](MODULE_LIFECYCLE.md) — install, disable, uninstall
- [contrib/README.md](../contrib/README.md) — in-repo module hub
