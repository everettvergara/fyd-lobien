# FYD CMS — Installed Public Themes

Runtime public themes live here. Vite builds assets from this folder; the active theme is selected in **Administration → Public Themes**.

## Fresh installs

`fyd-default/` is pre-installed so the public site works without a manual install step.

## Adding themes

1. Author under [`contrib_themes/`](../contrib_themes/)
2. Copy or install into `themes/{slug}/`
3. Run `npm run build`
4. Activate in admin

## Theme structure

```
themes/{slug}/
  theme.json              # must include regions[]
  assets/app.js
  scss/theme.scss
  js/Layouts/PublicLayout.vue
  js/Pages/Page/Show.vue  # generic public page shell
  js/Pages/Search.vue
  js/Components/…
```

Shared block components live in [`themes/_shared/`](../themes/_shared/) and are imported by each theme's `Page/Show.vue`.

## Public data contract (Inertia props)

Themes are **presentation only**. Page Manager resolves block data in PHP and passes structured regions to Inertia.

### Shared on every public page (`HandleInertiaRequests`)

| Prop | Source | Shape |
|------|--------|-------|
| `app` | Settings via `NavigationService` | `name`, `tagline`, `logo`, `favicon`, `contact`, `social` |
| `navigation` | Menus module | `header[]`, `footer[]` with `title`, `url`, `target`, `children` |
| `theme` | `ThemeService` | `slug`, `name` |
| `recaptcha` | config | `enabled`, `siteKey` |

### `Page/Show` (all public pages)

| Prop | Source |
|------|--------|
| `page` | Page Manager entry — `title`, `path`, `body`, `summary`, `featuredImage` |
| `content` | Linked published Content entry when the page was synced from Content (`PublicContent::entry()`), otherwise `null` |
| `regionOrder` | Ordered region keys from active theme |
| `regions` | Map of region → array of `{ id, type, component, props }` blocks |
| `seo` | `PublicSeo::fromModel()` on the page |

Block Vue components are resolved from `themes/_shared/blocks/` via `BlockRenderer`.

### `Search` page

| Prop | Source |
|------|--------|
| `query` | Request `q` parameter |
| `results` | `ContentSearchService` — content cards plus `type`, `typeLabel` |
| `seo` | `PublicSeo::defaults('Search')` |

## Regions

Define regions in `theme.json`:

```json
"regions": [
  { "key": "hero", "label": "Hero", "description": "Full-width top banner area" },
  { "key": "main", "label": "Main", "description": "Primary content column" },
  { "key": "footer", "label": "Footer", "description": "Above site footer" }
]
```

Page Manager places blocks into these regions. Themes do not reference modules directly.

## Banners

Reference banners by **key** in Page Manager blocks. See [`docs/BANNER_MODULE.md`](../docs/BANNER_MODULE.md).

## Related docs

- [`docs/PAGE_MANAGER.md`](../docs/PAGE_MANAGER.md) — public site architecture
- [`docs/THEME.md`](../docs/THEME.md) — theme authoring guide
