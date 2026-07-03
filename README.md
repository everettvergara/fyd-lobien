# FYD Laravel Bootstrap CMS

The official FYD Laravel Bootstrap CMS — a lightweight, modular, and AI-friendly content management platform for corporate websites, marketing pages, and landing pages.

## Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 13, MySQL/SQLite, Eloquent ORM |
| Admin Portal | Blade, Bootstrap 5, Bootstrap Icons |
| Public Website | Vue 3, Inertia.js, Bootstrap 5 |
| Asset Bundling | Vite |

## Requirements

- PHP 8.3+
- Composer 2.x
- Node.js 18+
- MySQL 8+ (production) or SQLite (development)

## Quick Start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan storage:link
php artisan serve
```

Visit:
- **Public website:** http://localhost:8000
- **Admin portal:** http://localhost:8000/admin

## Development

Run all services concurrently:

```bash
composer dev
```

Or separately:

```bash
php artisan serve
npm run dev
```

Run tests:

```bash
php artisan test
```

Fresh install with sample content:

```bash
php artisan migrate:fresh --seed
```

## Default Account

Password: `password`

| Role | Email |
|------|-------|
| Super Administrator | admin@fyd.local |

## Seeded Data

`php artisan migrate --seed` runs framework essentials first, then sample content.

### Framework essentials

| Data | Items |
|------|-------|
| Permissions & roles | RBAC for all modules (5 system roles) |
| Settings | Generic defaults (`Your Website` branding, auth, email, media, SEO, cache) |
| Banner templates | 12 system layout templates |
| Address data | Philippine provinces and cities |
| Admin user | `admin@fyd.local` |
| Maintenance page | `/site-maintenance` |
| Content types | `page`, `article` (via migration) |

### Sample content

| Content | Items |
|---------|-------|
| Pages | About, Services, Contact, Privacy Policy, Terms of Service |
| Articles | 4 published sample articles |
| Banners | 1 sample banner per template (12 total) |
| Menus | Header and footer navigation |

Full seeder inventory: [docs/SEEDING.md](docs/SEEDING.md)

| Area | URL |
|------|-----|
| Homepage | `/` |
| Search | `/search` |
| Admin Dashboard | `/admin` |
| Admin Login | `/admin/login` |

## Project Structure

```
app/
├── Enums/              # Shared enums (UserStatus, ContentStatus, etc.)
├── Http/
│   ├── Controllers/
│   │   └── Public/     # Public Inertia controllers
│   └── Middleware/
├── Models/             # Framework/kernel models (User, Role, Media, etc.)
├── Modules/            # Core CMS modules + installed business modules
│   ├── Authentication/
│   ├── Content/
│   └── ...
├── Services/           # Shared framework services
├── Support/            # Helper classes
└── Traits/             # Shared traits (HasRoles, HasSeo, Publishable)

contrib/                # Installable business module source (copy → app/Modules/)
contrib_themes/         # Public theme source (copy → themes/)
themes/                 # Installed public themes (Vite + activation)
docs/                   # Authoritative architecture documentation
resources/
├── admin/              # Admin theme SCSS/JS
├── scss/               # Admin-only shared tokens
└── views/
    ├── admin/          # Admin Blade layouts
    └── app.blade.php   # Inertia root template

routes/
├── web.php             # Public routes
└── admin.php           # Admin route entry (modules load their own)
```

## Documentation

Authoritative documentation lives in [docs/](docs/README.md):

| Document | Description |
|----------|-------------|
| [ARCHITECTURE.md](docs/ARCHITECTURE.md) | Long-term architecture and principles |
| [FRAMEWORK.md](docs/FRAMEWORK.md) | Framework kernel modules and rules |
| [MODULE_STANDARD.md](docs/MODULE_STANDARD.md) | Required module structure |
| [MODULE_CONTRIBUTION.md](docs/MODULE_CONTRIBUTION.md) | Authoring business modules in `contrib/` |
| [MODULE_LIFECYCLE.md](docs/MODULE_LIFECYCLE.md) | Install, disable, uninstall |
| [contrib/README.md](contrib/README.md) | In-repo business module hub |
| [SECURITY.md](docs/SECURITY.md) | Security domain specification |
| [CONTENT_MODULE.md](docs/CONTENT_MODULE.md) | Unified content module and content types |
| [DEVELOPMENT.md](docs/DEVELOPMENT.md) | Day-to-day development guide |
| [SEEDING.md](docs/SEEDING.md) | Database seeders and new-install data |
| [VERSION.md](docs/VERSION.md) | CMS template version and changelog |
| [ROADMAP.md](docs/ROADMAP.md) | Phases, milestones, and current work |

## Module Standard

Every module follows the layout defined in [docs/MODULE_STANDARD.md](docs/MODULE_STANDARD.md).

- **Core modules** — listed in `config/modules.php`; always enabled
- **Business modules** — built in [`contrib/`](contrib/README.md), copied to
  `app/Modules/`, installed via **Administration → Modules**
  ([docs/MODULE_LIFECYCLE.md](docs/MODULE_LIFECYCLE.md))

Model placement rules: [docs/ADR/001-model-placement.md](docs/ADR/001-model-placement.md).

## Development Phases

| Phase | Status | Description |
|-------|--------|-------------|
| MVP | Complete | Foundation, auth, administration, CMS, public website |
| **Framework Stabilization** | **Current** | Framework services, security, standardization, docs, UI |
| Framework Modules | Planned | Module Registry, Menu Registry, Media Service, Audit Logs |
| Content Engine | Planned | Enhanced content workflows |
| Marketing / Commerce | Planned | Post-stabilization business modules |

See [docs/ROADMAP.md](docs/ROADMAP.md) for milestones and [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md) for development guidelines.

## License

Proprietary — FYD
