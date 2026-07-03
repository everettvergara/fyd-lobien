# FYD CMS — Public Theme Guide

> **Cursor / AI agents:** Read this document before any public-site styling, branding, color, font, typography, or theme task. Follow the rules in [Agent instructions](#agent-instructions-cursor--ai) below.

## Overview

FYD CMS uses **installable public themes**. Theme developers work in [`contrib_themes/`](../contrib_themes/); installed themes live in [`themes/`](../themes/). The active public theme is selected in **Administration → Public Themes** (`/admin/themes`).

| Portal | Stack | Assets |
|--------|-------|--------|
| Admin | Blade + Bootstrap 5 | `resources/admin/scss/app.scss` — **not affected** by public theme changes |
| Public | Vue 3 + Inertia.js + Bootstrap 5 | `themes/{slug}/scss/theme.scss` + `themes/{slug}/assets/app.js` |

Vite discovers all installed themes under `themes/` via [vite.config.js](../vite.config.js). Only the **active** theme's assets are loaded at runtime ([`ThemeService`](../app/Services/Theme/ThemeService.php)).

**Admin portal styling is independent.** Switching the public theme does not change admin colors, typography, or layout.

---

## Directory layout

Parallel to business modules (`contrib/` → `app/Modules/`):

| Modules | Public themes |
|---------|---------------|
| `contrib/{Name}/` | `contrib_themes/{slug}/` |
| `app/Modules/{Name}/` | `themes/{slug}/` |

```text
fyd-web2/
├── contrib_themes/       ← authoring source (develop here)
│   └── fyd-default/      ← canonical reference theme
├── themes/               ← runtime install (Vite builds from here)
│   └── fyd-default/      ← pre-installed on fresh clones
└── resources/admin/      ← admin theme (unchanged)
```

| Path | Role |
|------|------|
| `contrib_themes/{slug}/` | Source package — not built until installed |
| `themes/{slug}/` | Installed theme — Vite entry points, activation target |
| `themes/fyd-default/` | Bundled default — always present as fallback |

**Workflow:** develop in `contrib_themes/` → install (copy to `themes/` or admin **Install**) → `npm run build` → activate in admin.

See also [contrib_themes/README.md](../contrib_themes/README.md) and [themes/README.md](../themes/README.md).

---

## Theme folder contract

Each theme at `contrib_themes/{slug}/` or `themes/{slug}/`:

```text
{slug}/
  theme.json              # manifest
  assets/
    app.js                # Inertia/Vue entry
  scss/
    _design-tokens.scss     # colors, typography, spacing
    _tokens-root.scss       # --fyd-* CSS custom properties
    theme.scss              # Bootstrap compile + layout classes
  js/
    bootstrap.js
    Layouts/PublicLayout.vue
    Pages/Home.vue
    Pages/Content/Show.vue
    Pages/Search.vue
    Pages/Webforms/Show.vue          # optional — falls back to fyd-default
    Pages/Careers/Show.vue             # optional — falls back to fyd-default
    Components/             # BannerRenderer, NavMenu, etc.
    composables/
  README.md
```

### `theme.json`

```json
{
    "name": "FYD Default",
    "slug": "fyd-default",
    "version": "1.0.0",
    "description": "Default corporate public theme",
    "author": "FYD",
    "assets": {
        "scss": "scss/theme.scss",
        "js": "assets/app.js"
    },
    "protected": true
}
```

- `protected: true` on `fyd-default` — always available; cannot be removed from runtime.
- [`ThemeRegistryService`](../app/Services/Theme/ThemeRegistryService.php) validates manifest, asset files, and Inertia pages.

### Validation: errors vs warnings

Themes are **blocked** only when core requirements fail. Missing optional module support produces **warnings** only — the theme remains installable and selectable.

| Check | Severity | Blocks install / activation |
|-------|----------|----------------------------|
| `theme.json` keys (`name`, `slug`, `version`, `assets`) | Error | Yes |
| SCSS and JS entry files | Error | Yes |
| Core pages: `Home.vue`, `Content/Show.vue`, `Search.vue` | Error | Yes |
| Module pages: `Webforms/Show.vue`, `Careers/Show.vue` | Warning | No |
| Module components: `CareerApplicationForm.vue`, `CareersListingRenderer.vue`, `NewsletterRenderer.vue` | Warning | No |

When a module page is missing from the active theme, the public site **falls back at runtime** to the same page from `fyd-default` via [`themes/_shared/resolveInertiaPage.js`](../themes/_shared/resolveInertiaPage.js). Copy module pages/components into your theme when you want them styled with that theme's layout and tokens.

---

## Quick start — retheme the public site

**Edit tokens in the active theme:**

[`themes/fyd-default/scss/_design-tokens.scss`](../themes/fyd-default/scss/_design-tokens.scss)

(or `themes/{your-slug}/scss/_design-tokens.scss` after installing a custom theme)

When maintaining the default theme, also update the canonical source at `contrib_themes/fyd-default/scss/_design-tokens.scss`.

```bash
npm run dev    # or npm run build
```

For layout or component changes beyond tokens, edit files under `themes/{slug}/js/` and `themes/{slug}/scss/theme.scss`.

**Rule:** Global public look → tokens + `theme.scss`. One-off styling → Vue Bootstrap classes or scoped styles.

---

## Design tokens reference

Public tokens live in `themes/{slug}/scss/_design-tokens.scss`. They map to Bootstrap Sass variables in `theme.scss` and to `--fyd-*` CSS variables via `_tokens-root.scss`.

### Brand colors

| Token | Default | Purpose |
|-------|---------|---------|
| `$fyd-color-primary` | `#2563eb` | Links, buttons, accents |
| `$fyd-color-primary-dark` | `#1d4ed8` | Link hover states |
| `$fyd-color-dark` | `#1e293b` | Hero, footer backgrounds |
| `$fyd-color-dark-mid` | `#334155` | Gradient stops |
| `$fyd-color-dark-light` | `#475569` | Gradient stops |
| `$fyd-color-text` | `#0f172a` | Body text |
| `$fyd-color-text-muted` | `#64748b` | Lead, meta, blockquotes |
| `$fyd-color-bg` | `#ffffff` | Page background |
| `$fyd-color-bg-alt` | `#f8fafc` | Alt sections (`.public-section-alt`) |
| `$fyd-color-border` | `#e2e8f0` | Dividers |

Admin brand colors are **separate** — frozen in [`resources/scss/_design-tokens-public.scss`](../resources/scss/_design-tokens-public.scss) for the admin bundle only.

### Typography (public only)

| Token | Default | Purpose |
|-------|---------|---------|
| `$fyd-font-heading` | system-ui stack | h1–h6, display headings |
| `$fyd-font-body` | system-ui stack | Body text |
| `$fyd-font-size-body` | `1rem` | Base / paragraph |
| `$fyd-font-size-lead` | `1.25rem` | `.lead` summaries |
| `$fyd-font-size-h1` … `$fyd-font-size-h6` | `2.5rem` … `1rem` | Heading scale |
| `$fyd-line-height-body` | `1.6` | Paragraph readability |
| `$fyd-line-height-heading` | `1.2` | Headings |

Bootstrap classes (`text-primary`, `btn-primary`, `display-5`, `lead`) pick up token values automatically after compile.

### Layout (public only)

| Token | Default | Purpose |
|-------|---------|---------|
| `$fyd-section-padding-y` | `4rem` | `.public-section` |
| `$fyd-hero-padding-y` | `6rem` | `.public-hero`, `.public-banner` |

### CSS custom properties

`_tokens-root.scss` emits matching `--fyd-*` variables on `:root`. The Inertia progress bar in `assets/app.js` reads `--fyd-color-primary` at runtime.

### Layout classes (`theme.scss`)

Common public classes defined in `theme.scss`:

- `.public-hero`, `.public-banner`, `.public-section`, `.public-section-alt`
- `.public-footer`, `.site-brand-title`, `.feature-icon`
- `.content-body` — rich HTML typography for CMS content and banner rich text

When HTML is rendered via `v-html`, wrap it in `.content-body`.

---

## Public site composition (Page Manager)

Themes are **presentation only**. They define **regions** in `theme.json` and render blocks placed by Page Manager. See [PAGE_MANAGER.md](PAGE_MANAGER.md).

```text
Page Manager → block resolvers → Inertia props → theme Page/Show.vue → BlockRenderer
```

| Concern | Owner |
|---------|-------|
| Page paths, body, SEO | Page Manager |
| Region layout | Active theme |
| Block data | Module block resolvers |
| Block placement | Page Manager admin (drag-drop) |

Themes do **not** import module Vue components. `BlockRenderer` resolves components from `themes/_shared/blocks/`.

> **Deprecated:** Page attachments, section attachments, and hardcoded Inertia props per module on `Content/Show` are removed. Modules expose **blocks** instead.

---

## Create and install a theme

### Scaffold

```bash
php artisan make:theme AcmeCorp
```

Copies `contrib_themes/fyd-default/` → `contrib_themes/acme-corp/` and updates `theme.json`.

### Install to runtime

**Admin:** Administration → Public Themes → **Available** → **Install**

**Manual:**

```bash
# Linux / macOS
cp -r contrib_themes/acme-corp themes/acme-corp

# Windows (PowerShell)
Copy-Item -Recurse contrib_themes\acme-corp themes\acme-corp
```

Then:

```bash
npm run build
```

Activate under **Administration → Public Themes → Installed**.

### Vue import convention

Theme Vue files use **relative imports** within `js/` (e.g. `../../Components/BannerRenderer.vue` from nested pages). Do not use the `@` alias — it is reserved for legacy/admin paths.

Inertia pages resolve from `themes/{slug}/js/Pages/` via each theme's `assets/app.js`. Missing optional module pages (`Webforms/Show`, `Careers/Show`) automatically fall back to `themes/fyd-default/js/Pages/` at runtime.

Each theme's `assets/app.js` declares the page globs (Vite resolves `import.meta.glob` relative to that file) and delegates to [`themes/_shared/resolveInertiaPage.js`](../themes/_shared/resolveInertiaPage.js).

---

## Configuration and services

| Item | Location |
|------|----------|
| Theme paths | [`config/fyd.php`](../config/fyd.php) → `themes.path`, `themes.contrib_path`, `themes.default` |
| Active theme setting | `appearance.active_theme` in settings table (default `fyd-default`) |
| Registry | [`ThemeRegistryService`](../app/Services/Theme/ThemeRegistryService.php) — discovery and validation |
| Runtime | [`ThemeService`](../app/Services/Theme/ThemeService.php) — active slug, Vite assets, install, activate |
| Inertia page paths | [`config/inertia.php`](../config/inertia.php) — testing resolves pages from active theme |
| Admin module | [`app/Modules/Themes/`](../app/Modules/Themes/) — `/admin/themes` |

Permissions: `themes.view`, `themes.activate`, `themes.install`

On activation: page block migration runs when region layouts differ → setting saved → public cache cleared → audit log entry.

### Block migration on theme activation

Page Manager blocks are stored globally by `region_key`, not per theme. When you activate a different installed theme, [`ThemeBlockMigrationService`](../app/Services/Theme/ThemeBlockMigrationService.php) runs automatically:

| Block `region_key` | Action |
|--------------------|--------|
| Exists in **both** old and new theme | Kept unchanged |
| Exists only on old theme | Remapped using new theme `region_map`, else moved to `main`, else first region |

Migration applies to both `page_blocks` and `page_master_blocks`. The admin success message reports preserved and moved counts.

Optional explicit remaps in `theme.json`:

```json
"region_map": {
  "sidebar": "main",
  "promo": "hero"
}
```

Keys are region keys from the **previous** theme; values must be region keys defined in **this** theme's `regions` array. Validated at theme install time.

Installing a theme from contrib does **not** migrate blocks until that theme is activated.

---

## Build and verify

```bash
npm run dev      # development with HMR
npm run build    # production — builds ALL themes under themes/

php artisan test --filter=ThemeLifecycleTest
php artisan test --filter=ThemeBlockMigrationTest
php artisan test --filter=PublicWebsiteTest
```

Spot-check after token or layout changes:

- **Public:** home hero, section headings, footer, `.content-body`, primary buttons
- **Public:** Inertia progress bar matches `$fyd-color-primary`
- **Admin:** unchanged regardless of active public theme
- **Admin UI:** Public Themes lists installed/available themes; activation switches public layout/CSS/JS

---

## What is NOT themed (by design)

- Admin portal SCSS, layouts, and Vite bundle
- Admin appearance settings UI for public themes (activation only, not token editing in admin)
- Dark/light mode toggle
- Theme zip upload from admin (dev drops folder + `npm run build`)
- Automatic module API discovery — new modules need explicit public controller/facade wiring

---

## Agent instructions (Cursor / AI)

When working on **public** styling or themes:

1. **Read this document first.**
2. **Public changes** go in `contrib_themes/{slug}/` (source) and/or `themes/{slug}/` (runtime) — not in `resources/js/` or `resources/scss/public.scss` (removed).
3. **Retheme globally** → edit `scss/_design-tokens.scss` in the theme folder, then `npm run build`.
4. **Layout/components** → edit `theme.scss` or Vue files under `js/`.
5. **Do not modify** `resources/admin/` for public theme work.
6. **Do not duplicate** module data fetching in Vue — use Inertia props from controllers.
7. **New theme** → `php artisan make:theme`, install to `themes/`, build, document any new banner keys in admin.
8. After changes, verify with `ThemeLifecycleTest` and `PublicWebsiteTest`.

When working on **admin** styling:

- Edit `resources/admin/scss/app.scss` and admin Blade components only.
- Admin brand colors come from `resources/scss/_design-tokens-public.scss` — independent of the active public theme.

---

## Related documentation

| Document | Topic |
|----------|-------|
| [themes/README.md](../themes/README.md) | Installed themes, full Inertia prop contract |
| [contrib_themes/README.md](../contrib_themes/README.md) | Authoring and install workflow |
| [BANNER_MODULE.md](BANNER_MODULE.md) | Banner keys and DTO shapes |
| [CONTENT_MODULE.md](CONTENT_MODULE.md) | Content pages and public routes |
| [DEVELOPMENT.md](DEVELOPMENT.md) | Portals, modules, testing |
| [FRAMEWORK.md](FRAMEWORK.md) | Theme Manager framework service |
