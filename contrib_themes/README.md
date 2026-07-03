# FYD CMS — Public Theme Contributions

Author installable **public website themes** here, parallel to business modules in [`contrib/`](../contrib/).

## Layout

| Path | Role |
|------|------|
| **`contrib_themes/{slug}/`** | Build and maintain theme source here |
| **`themes/{slug}/`** | Runtime install path — copy here, then activate in admin |

Themes in `contrib_themes/` are **not built by Vite** until copied into `themes/`.

## Default theme

`fyd-default/` is the canonical reference theme. It also exists pre-installed under `themes/fyd-default/`.

## Create a new theme

```bash
php artisan make:theme AcmeCorp
```

This copies `contrib_themes/fyd-default/` to `contrib_themes/acme-corp/` and updates `theme.json`.

## Install a theme

```bash
# Linux / macOS
cp -r contrib_themes/acme-corp themes/acme-corp

# Windows (PowerShell)
Copy-Item -Recurse contrib_themes\acme-corp themes\acme-corp
```

Or use **Administration → Public Themes → Available → Install**.

Then:

```bash
npm run build
```

Activate under **Administration → Public Themes**.

## Required structure

Each theme folder must include:

- `theme.json` — manifest (`name`, `slug`, `version`, `assets`)
- `assets/app.js` — Inertia/Vue entry (uses shared page resolver in `themes/_shared/`)
- `scss/theme.scss` — Bootstrap + layout styles
- `js/Pages/Home.vue`, `js/Pages/Content/Show.vue`, `js/Pages/Search.vue`

Optional module pages and components (`Webforms/Show`, `Careers/Show`, `CareerApplicationForm`, etc.) are recommended when you install the matching business modules. If they are missing, **Administration → Public Themes** shows warnings but still allows install and activation; the public site falls back to `fyd-default` for missing module pages.

See [`themes/README.md`](../themes/README.md) for the public data contract (Inertia props from Content, Banners, Menus, Settings modules).
