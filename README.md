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

Fresh install with demo data:

```bash
php artisan migrate:fresh --seed
```

## Default Accounts

All demo accounts use password: `password`

| Role | Email |
|------|-------|
| Super Administrator | admin@fyd.local |
| Editor | editor@fyd.local |
| Author | author@fyd.local |
| Viewer | viewer@fyd.local |

## Demo Content

After seeding, the public website includes:

| Content | Items |
|---------|-------|
| Pages | About, Services, Contact, Privacy Policy, Terms |
| Blog Posts | 4 published articles |
| Banners | Homepage hero + 2 carousel slides |
| Menus | Header and footer navigation |
| Settings | Contact info, social links, SEO defaults |

## URLs

| Area | URL |
|------|-----|
| Homepage | `/` |
| Blog | `/blog` |
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
├── Modules/            # CMS modules (see docs/MODULE_STANDARD.md)
│   ├── Authentication/
│   ├── Dashboard/
│   ├── Users/
│   ├── Roles/
│   ├── Permissions/
│   ├── Pages/
│   ├── Posts/
│   ├── Banners/
│   ├── Menus/
│   ├── Media/
│   ├── Settings/
│   └── SEO/
├── Services/           # Shared framework services
├── Support/            # Helper classes
└── Traits/             # Shared traits (HasRoles, HasSeo, Publishable)

docs/                   # Authoritative architecture documentation
resources/
├── admin/              # Admin theme SCSS/JS
├── js/                 # Public Inertia/Vue app
│   ├── Components/
│   ├── Layouts/
│   └── Pages/
├── scss/               # Public theme styles
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
| [SECURITY.md](docs/SECURITY.md) | Security domain specification |
| [DEVELOPMENT.md](docs/DEVELOPMENT.md) | Day-to-day development guide |
| [ROADMAP.md](docs/ROADMAP.md) | Phases, milestones, and current work |

## Module Standard

Every module follows the layout defined in [docs/MODULE_STANDARD.md](docs/MODULE_STANDARD.md).
Enable modules in `config/modules.php`.

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
