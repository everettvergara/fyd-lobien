# Lobien Realty Group Theme

Public theme for FYD CMS styled after [lobiengroup.com](https://www.lobiengroup.com/).

## Workflow

1. **Develop** in `contrib_themes/lobien/` (this directory)
2. **Install** to runtime: `Copy-Item -Recurse contrib_themes\lobien themes\lobien`
3. **Build**: `npm run build`
4. **Activate** under Administration → Public Themes

Do not edit `themes/lobien/` directly during development — copy from contrib when installing.

## Overview

This theme replicates the Lobien homepage layout: property search hero, listing category grid, agent consult block, news articles, market outlook form, and property search link sections. Property-specific sections use **static placeholder markup** until a Properties module is available.

The homepage is rendered via `Page/Show.vue` when `page.path === '/'`. It composes static Lobien sections and maps the CMS `latest-articles` content block into the "What's New?" grid.

## Banner keys (admin)

Configure these banner keys in **Administration → Banners**:

| Key | Usage |
|-----|--------|
| `homepage-hero` | Hero background on the homepage (referenced by Page Manager home block) |
| `homepage-slider` | Optional carousel/slider banner |

## Homepage sections

1. **Property search hero** — banner image + static search form + contact CTA bar
2. **Our Listings** — 8 property category tiles (static links)
3. **Consult with an Agent** — static property select + contact link
4. **What's New?** — `latest-articles` content block from Page Manager
5. **Market Outlook Reports** — static download form (visual only)
6. **Property search links** — For Sale / For Lease city link lists (static)

`featured-pages` and the hero `banner` block are hidden on the homepage — their data is consumed by `PropertySearchHero` and `LobienWhatsNewSection` instead.

## Localhost content mapping

Uses existing seeded content (no Lobien-specific seeders):

| Local URL | Lobien treatment |
|-----------|------------------|
| `/` | Lobien home sections + latest articles |
| `/about`, `/services`, `/contact` | Banner + page header/body with Lobien typography |
| `/articles` | Lobien-styled article index |
| `/articles/{slug}` | Lobien article detail layout |

## Design tokens

Edit [`scss/_design-tokens.scss`](scss/_design-tokens.scss) for global retheming:

- Primary blue: `#004aad`
- Navy: `#121643`
- Accent red: `#ba0d26`

Run `npm run build` after token changes (once installed to `themes/`).

## Install

```powershell
# Windows
if (Test-Path themes\lobien) { Remove-Item -Recurse -Force themes\lobien }
Copy-Item -Recurse contrib_themes\lobien themes\lobien

# Linux / macOS
rm -rf themes/lobien && cp -r contrib_themes/lobien themes/lobien
```

Then:

```bash
npm run build
```

Activate under **Administration → Public Themes → Installed**.

Or use **Administration → Public Themes → Available → Install**.

## Verify

```bash
php artisan test --filter=ThemeLifecycleTest
php artisan test --filter=PublicWebsiteTest
```

Spot-check: homepage section order, nav/footer, article cards, responsive layout.

## Notes

- Listing category images reference lobiengroup.com CDN URLs by default.
- Property search forms submit to `#` (no backend).
- Market outlook form is visual-only; wire to WebForms later if needed.
- Typography uses Century Gothic / Trebuchet MS as Futura fallbacks.
