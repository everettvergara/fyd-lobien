# Contributing a Business Module

This guide is for authors who build **installable business modules** in the
[`contrib/`](../contrib/) directory or publish them as standalone repositories
(e.g. `fyd-module-commerce`).

## Terminology

| Term | Meaning |
|------|---------|
| **Business module** | One installable package → one sidebar **group** (E-Commerce, Careers) |
| **Feature module** | One admin area inside a business module → one **menu link** under that group |
| **Core module** | Bundled with FYD CMS; not installable |

## Where to build

In the fyd-web2 repository, create:

```text
contrib/{ModuleName}/
```

Example: `contrib/Commerce/`, `contrib/Careers/`, `contrib/DemoNotes/`.

For external distribution, the Git repo root **is** that folder (same contents
as `contrib/Commerce/`).

## Required layout

Installable modules use the **strict** layout (no legacy root `Migrations/`):

```text
Commerce/
├── Module.php
├── module.json
├── README.md
├── Controllers/
├── Models/
├── Requests/
├── Policies/
├── Services/
├── Routes/
│   ├── admin.php
│   └── web.php              # optional
├── Views/                   # commerce::orders.index
├── Database/
│   ├── Migrations/          # every file must implement down()
│   ├── Seeders/
│   └── data/                # optional
├── Console/                 # optional
├── Tests/Feature/
└── lang/                    # optional
```

## Namespace

Composer maps `App\` → `app/`, so use:

```text
App\Modules\{ModuleName}\{Class}
```

| Item | Convention | Example |
|------|------------|---------|
| Folder / `Module::name()` | PascalCase | `Commerce` |
| Blade views | lowercase | `commerce::orders.index` |
| Routes | `admin.*` | `admin.orders.index` |
| Permissions | lowercase domain | `orders.view` |

Models stay in the module per [ADR 001](ADR/001-model-placement.md). Do not
add business models to `app/Models/`.

## module.json

Required for contributions:

```json
{
  "name": "Commerce",
  "slug": "commerce",
  "version": "1.0.0",
  "description": "Orders and checkout for FYD CMS.",
  "group": "E-Commerce",
  "group_icon": "bi-cart",
  "group_sort": 10,
  "author": "Your Name",
  "license": "MIT",
  "fyd_cms": ">=1.0.0",
  "requires_core": ["Media", "Settings", "SEO"],
  "autoload": "App\\Modules\\Commerce",
  "features": [
    {
      "label": "Order Processing",
      "permission": "orders.view",
      "route": "admin.orders.index",
      "icon": "bi-receipt",
      "sort": 10
    },
    {
      "label": "Products",
      "permission": "products.view",
      "route": "admin.products.index",
      "icon": "bi-box-seam",
      "sort": 20
    }
  ]
}
```

| Field | Required | Purpose |
|-------|----------|---------|
| `group` | Yes | Right-sidebar business group title |
| `group_icon` | No | Bootstrap Icon on group header |
| `group_sort` | No | Order among business groups |
| `features` | Recommended | Feature modules under the group; validated against `menuItems()` on install |
| `requires_core` | Recommended | Core modules this package depends on |

## Module.php

Single registration manifest — no scattered wiring:

```php
namespace App\Modules\Commerce;

class Module extends \App\Framework\Module
{
    public function name(): string { return 'Commerce'; }
    public function isInstallable(): bool { return true; }
    public function version(): string { return '1.0.0'; }
    public function description(): string { return 'Orders and checkout.'; }
    public function group(): string { return 'E-Commerce'; }
    public function groupIcon(): string { return 'bi-cart'; }
    public function groupSort(): int { return 10; }

    public function policies(): array { /* ... */ }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('orders', 'view', 'View Orders'),
            // one domain per feature as needed
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Order Processing', 'admin.orders.index', 'orders.view', 'bi-receipt', sort: 10),
            $this->menuItem('Products', 'admin.products.index', 'products.view', 'bi-box-seam', sort: 20),
        ];
        // section = group() automatically; panel = business (right sidebar)
    }

    public function seeders(): array { return []; }
    public function commands(): array { return []; }
    public function uninstall(): void { /* optional non-schema cleanup */ }
}
```

Do **not** pass a custom `section` on `menuItem()` for installable modules.

## Self-contained wiring

| Concern | Where | Must NOT require |
|---------|-------|------------------|
| Schema | `Database/Migrations/` | Manual registration |
| Seeders | `Module::seeders()` | `DatabaseSeeder.php` |
| Permissions / menus | `Module.php` | Sidebar edits |
| Routes | `Routes/admin.php` | `routes/admin.php` |
| Commands | `Module::commands()` | `bootstrap/app.php` |

Use framework services (Media, Settings, Audit, Standard Admin List) per
[FRAMEWORK.md](FRAMEWORK.md). Do not depend on other **installable** business
modules — only **core** modules.

## Feature modules inside one package

Suggested folder layout for E-Commerce:

```text
Commerce/
├── Controllers/
│   ├── OrderController.php
│   ├── ProductController.php
│   └── CategoryController.php
├── Services/
│   ├── OrderAdminListService.php
│   └── ProductAdminListService.php
├── Views/
│   ├── orders/
│   └── products/
└── Routes/admin.php
```

Each feature: Controller, Service, Policy, Requests, views, permission domain.

## Migrations and uninstall

- All migrations must implement reversible `down()` methods
- Uninstall rolls back migrations under the module path
- Test uninstall in a fresh database before publishing

## Publish checklist

- [ ] Module under `contrib/{Name}/` with strict layout
- [ ] `Module.php` + `module.json`; `isInstallable(): true`
- [ ] `group` set; `features[]` matches `menuItems()`
- [ ] All migrations have `down()`
- [ ] `publicBlocks()` lists every Page Manager block key; feature test confirms uninstall removes blocks from page regions
- [ ] README: copy path, `requires_core`, permissions, seed notes
- [ ] Uses framework services; no duplicated kernel logic
- [ ] Feature tests runnable when copied to `app/Modules/`

## Sample module

See `contrib/DemoNotes/` (when present) — minimal business module with two
features under group **Demo Module**. Walkthrough:
[MODULE_LIFECYCLE.md](MODULE_LIFECYCLE.md).

## Related

- [contrib/README.md](../contrib/README.md)
- [MODULE_STANDARD.md](MODULE_STANDARD.md)
- [MODULE_LIFECYCLE.md](MODULE_LIFECYCLE.md)
- [PAGE_MANAGER.md](PAGE_MANAGER.md) — public blocks and Page Manager integration

## Public blocks

Installable modules expose **blocks** for the Page Manager palette. Modules must **not** attach themselves to pages or theme regions.

### Register blocks in `Module.php`

```php
public function publicBlocks(): array
{
    return [
        PublicBlock::make('webform')
            ->label('Web Form')
            ->icon('bi-ui-checks')
            ->module($this->name())
            ->resolver(WebformBlockResolver::class)
            ->component('WebformBlock')
            ->configSchema([
                [
                    'key' => 'webform_slug',
                    'label' => 'Web Form',
                    'type' => 'select',
                    'required' => true,
                    'optionsProvider' => WebformOptionsProvider::class,
                ],
            ]),
    ];
}
```

Any block with settings **must** declare `configSchema()`. Do not rely on raw `key=value` config in admin — Page Manager renders typed fields from the schema.

### Config options provider

For `select` fields backed by database records, implement `App\Contracts\BlockConfigOptionsProvider`:

```php
class WebformOptionsProvider implements BlockConfigOptionsProvider
{
    public function options(): array
    {
        return Webform::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['slug', 'name'])
            ->map(fn (Webform $form) => [
                'value' => (string) $form->slug,
                'label' => (string) $form->name,
            ])
            ->values()
            ->all();
    }
}
```

Reference the provider class in the schema field's `optionsProvider` key. Page Manager resolves options server-side when loading the page editor.

See [PAGE_MANAGER.md](PAGE_MANAGER.md) for the full field contract.

### Or in `module.json`

```json
"blocks": [
  {
    "key": "webform",
    "label": "Web Form",
    "icon": "bi-ui-checks",
    "component": "WebformBlock",
    "resolver": "App\\Modules\\WebForms\\Blocks\\WebformBlockResolver"
  }
]
```

### Block resolver

Implement `App\Contracts\BlockResolver`:

```php
public function resolve(array $config, Page $page): array
{
    return ['slug' => $config['webform_slug']];
}
```

The Vue `component` key is rendered by the theme's shared `BlockRenderer`.

### Uninstall cleanup

When a module is uninstalled, [`ModulePageBlockCleanupService`](../app/Services/Module/ModulePageBlockCleanupService.php) deletes all `page_blocks` and `page_master_blocks` rows whose `block_type` matches keys returned by `publicBlocks()`. **Every block your module registers must appear in `publicBlocks()`** so placements in footer, main, hero, sidebar, and other regions are removed automatically.

- Do **not** hand-delete blocks in `Module::uninstall()` — registration is the contract.
- Do **not** auto-attach blocks to pages or regions on install.
- Add a feature test (see [`WebFormsModuleTest`](../tests/Feature/WebFormsModuleTest.php) or [`NewsletterModuleTest`](../tests/Feature/NewsletterModuleTest.php)) that creates page and Page Master blocks, uninstalls the module, and asserts both tables are clean.

### Do not

- Add page attachment or section attachment admin UI
- Register `*AttachmentResolver` contracts
- Hardcode public routes for composed pages (JSON API routes for block hydration are OK)
