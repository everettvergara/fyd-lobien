# Module Lifecycle

This document describes how **installable business modules** are discovered,
installed, disabled, and uninstalled in FYD CMS.

> **Status:** Module Manager services and the **Administration → Modules** page
> are part of the business-module framework work. Until implemented, core modules
> remain enabled via `config/modules.php` only.

## Module tiers

| Tier | Location | Install? | Admin sidebar |
|------|----------|----------|---------------|
| **Core module** | `app/Modules/` + `config/modules.php` `core[]` | Always on | Left panel (Content, Administration, …) |
| **Business module** | `contrib/` (source) → `app/Modules/` (runtime) | Opt-in via Modules page | Right panel — one **group** per business module |
| **Feature module** | Inside a business module package | Not separate | Menu link under the business **group** |

Example:

```text
Business module: E-Commerce          ← install once
├── Order Processing                 ← feature (menu item)
├── Products
├── Categories
├── Colors
├── Payments
└── Reports
```

Install, disable, and uninstall apply to the **whole business module**, not to
individual features.

## Paths

```text
fyd-web2/
├── contrib/Commerce/     ← build here (see contrib/README.md)
└── app/Modules/Commerce/ ← copy here for runtime discovery
```

The Module Manager scans **`app/Modules/*/Module.php`** only. Folders under
`contrib/` are ignored until copied.

## Install procedure (site owner)

### 1. Get the module

- **In this repo:** use a folder under [`contrib/`](../contrib/)
- **External site:** download `fyd-module-{slug}` and copy into `contrib/` or
  directly into `app/Modules/`

The folder root must contain `Module.php` and `module.json`.

### 2. Copy to runtime path

```bash
cp -r contrib/Commerce app/Modules/Commerce
```

Do **not** add the module to `config/modules.php`. Do **not** edit
`DatabaseSeeder.php` or `bootstrap/app.php`.

### 3. Discover and install

1. Log in as a user with **`modules.install`** (super-admin by default)
2. Go to **Administration → Modules** (`/admin/modules`)
3. Click **Rescan** if the module does not appear
4. Find the module under status **Available**
5. Click **Install** and confirm the modal (migrations, seeders, permissions,
   sidebar **group**, and **features** are listed)
6. Assign new permissions via **Administration → Roles**

After install, the business **group** (e.g. `E-Commerce`) appears in the
**right sidebar panel** with one link per feature the user may access.

### 4. CLI alternative

```bash
php artisan module:install Commerce
php artisan module:disable Commerce    # confirms unless --force
php artisan module:enable Commerce
php artisan module:uninstall Commerce  # confirms + type name unless --force
```

## Lifecycle actions

| Action | Data | Routes / menus | Schema |
|--------|------|----------------|--------|
| **Install** | Seeded if configured | Active — group in right panel | Migrations run |
| **Disable** | Preserved | Hidden — group removed | Unchanged |
| **Enable** | Unchanged | Restored | Unchanged |
| **Uninstall** | Removed | Removed | Tables dropped (migration rollback) |

## Confirmation requirements

Every **disable** and **uninstall** requires explicit confirmation.

### Disable

- **Admin UI:** Modal shows business group name, features that will disappear,
  and that data is kept. User must click **Confirm Disable** or **Cancel**.
- **CLI:** Interactive prompt; use `--force` only for scripts/CI.

### Uninstall

- **Admin UI:** Destructive warning, affected tables/features listed. User must
  **type the module name** (e.g. `Commerce`) before **Confirm Uninstall**.
- **CLI:** Same pattern; `--force` for non-interactive use only.

Disable and uninstall use **POST** requests with CSRF — never one-click GET
links. All actions are audit-logged.

## Modules admin page

**Location:** Administration → Modules (core left sidebar)

**List columns:** Name, Group, Features, Version, Status, Description, Requires,
Actions

**Statuses:**

| Status | Actions |
|--------|---------|
| **Available** | Install |
| **Installed** | Disable, Uninstall |
| **Disabled** | Enable, Uninstall |

**Empty state:** “Copy a module from `contrib/{Name}/` to `app/Modules/{Name}/`
and click Rescan.”

## Permissions

| Permission | Purpose |
|------------|---------|
| `modules.view` | Open Modules page |
| `modules.install` | Install Available modules |
| `modules.disable` | Disable Installed modules |
| `modules.enable` | Re-enable Disabled modules |
| `modules.uninstall` | Uninstall (destructive) |

Feature-level permissions (e.g. `products.view`) are created when the business
module is installed and assigned via Roles.

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Not in Available list | Verify `app/Modules/{Name}/Module.php` + `module.json`; `isInstallable(): true` |
| Install button disabled | Check `module.json` → `fyd_cms` version vs site version |
| Install fails | Read flash message / logs; fix module migrations |
| Sidebar group missing | Assign feature permissions to the user’s role |
| After copy, still missing | **Rescan** or `php artisan optimize:clear` |

## Related

- [MODULE_CONTRIBUTION.md](MODULE_CONTRIBUTION.md) — authoring modules in `contrib/`
- [MODULE_STANDARD.md](MODULE_STANDARD.md) — folder layout
- [contrib/README.md](../contrib/README.md) — in-repo module hub
