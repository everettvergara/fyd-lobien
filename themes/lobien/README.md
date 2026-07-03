# Lobien Realty Group Theme

Public theme for FYD CMS styled after [lobiengroup.com](https://www.lobiengroup.com/).

## Overview

This theme replicates the Lobien homepage layout: property search hero, listing category grid, agent consult block, news articles, market outlook form, and property search link sections. Property-specific sections use **static placeholder markup** until a Properties module is available.

## Banner keys (admin)

Configure these banner keys in **Administration → Banners**:

| Key | Usage |
|-----|--------|
| `homepage-hero` | Fallback hero background when no slider is set |
| `homepage-slider` | Optional carousel/slider banner on the homepage |

## Homepage sections

1. **Property search hero** — banner image + static search form + contact CTA bar
2. **Our Listings** — 8 property category tiles (static links)
3. **Consult with an Agent** — static property select + contact link
4. **What's New?** — `latestArticles` from CMS (articles content type)
5. **Market Outlook Reports** — static download form (visual only)
6. **Property search links** — For Sale / For Lease city link lists (static)

Dynamic data comes from existing Inertia props (`hero`, `sliderBanner`, `latestArticles`, shared `app`, `navigation`). See [`themes/README.md`](../../themes/README.md).

## Design tokens

Edit [`scss/_design-tokens.scss`](scss/_design-tokens.scss) for global retheming:

- Primary blue: `#004aad`
- Navy: `#121643`
- Accent red: `#ba0d26`

Run `npm run build` after token changes (once installed to `themes/`).

## Install

```powershell
# Windows
Copy-Item -Recurse contrib_themes\lobien themes\lobien

# Linux / macOS
cp -r contrib_themes/lobien themes/lobien
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
- Market outlook form is visual-only; wire to WebForms later via `WebformRenderer` if needed.
- Typography uses Century Gothic / Trebuchet MS as Futura fallbacks (Futura is proprietary on the live site).
