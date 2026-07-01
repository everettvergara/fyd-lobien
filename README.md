# FYD Laravel Bootstrap CMS

The official FYD Laravel Bootstrap CMS — a lightweight, modular, and AI-friendly content management platform.

## Technology Stack

- **Backend:** Laravel 13, MySQL, Eloquent ORM
- **Admin Portal:** Blade, Bootstrap 5, Bootstrap Icons
- **Public Website:** Vue 3, Inertia.js, Bootstrap 5

## Requirements

- PHP 8.3+
- Composer
- Node.js 18+
- MySQL 8+ (or SQLite for development)

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

## Development

```bash
composer dev
```

Or run separately:

```bash
php artisan serve
npm run dev
```

## URLs

| Area | URL |
|------|-----|
| Public Website | http://localhost:8000 |
| Admin Portal | http://localhost:8000/admin |

## Project Structure

```
app/
├── Enums/                  # Shared enums
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Admin controllers
│   │   └── Public/         # Public Inertia controllers
│   └── Middleware/
├── Modules/                # CMS modules (Phase 2+)
└── Providers/
    └── ModuleServiceProvider.php

resources/
├── admin/                  # Admin theme assets (SCSS/JS)
├── js/                     # Public Inertia/Vue app
├── scss/                   # Public theme styles
└── views/
    ├── admin/              # Admin Blade layouts
    └── app.blade.php       # Inertia root template

routes/
├── web.php                 # Public routes
└── admin.php               # Admin routes (/admin)
```

## Development Phases

| Phase | Status | Description |
|-------|--------|-------------|
| 0 | Complete | Planning |
| 1 | Complete | Foundation (Laravel, Bootstrap, Themes, Layouts) |
| 2 | Pending | Authentication |
| 3 | Pending | Administration (Dashboard, Users, Roles, Permissions) |
| 4 | Pending | CMS (Pages, Blog, Banners, Menus, Media, Settings, SEO) |
| 5 | Pending | Public Website |
| 6 | Pending | MVP (Demo Data, Seeders, Documentation, Testing) |

## License

Proprietary — FYD
