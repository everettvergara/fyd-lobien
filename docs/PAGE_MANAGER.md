# FYD CMS — Page Manager

Authoritative specification for the public site composition system.

## Overview

The **Page Manager** core module is the single source of truth for the public website. It owns page paths, titles, body copy, SEO metadata, and block placement. Themes define **layout regions** only. Core and contrib modules expose **blocks** that admins drag into regions.

The admin portal is unaffected except for new Page Manager screens under **Content → Pages**.

## Concepts

| Concept | Description |
|---------|-------------|
| **Page Master** | Singleton catch-all defaults (SEO fallbacks + default blocks per region). Must be configured first. |
| **Page Entry** | A public URL (`/`, `/about`) with title, body, SEO, and optional per-page block overrides. |
| **Region** | Layout slot declared in the active theme's `theme.json`. |
| **Block** | Renderable unit exposed by a module (banner, webform, page body, etc.). |
| **Block config** | JSON settings for a placed block (e.g. `banner_key`, `webform_slug`). |

## Data model

- `page_masters` — singleton row with default SEO settings and `is_configured` flag
- `page_master_blocks` — default blocks per region
- `pages` — public pages (`path`, `title`, `slug`, `body`, publishing, `is_system`)
- `page_blocks` — per-page region block overrides

### Inheritance

When rendering a page, for each region:

1. If the page has one or more blocks in that region → use **page blocks only**
2. Otherwise → inherit **page master blocks** for that region

### Root page

- Path `/` always exists with `is_system = true`
- Cannot be deleted

## Public rendering

```
GET /*  →  PublicPageController
         →  PageManagerService::resolvePublishedPage($path)
         →  PageRenderService::render($page)
         →  Inertia::render('Page/Show', $payload)
```

Payload includes `page`, `regions`, `blocks` (hydrated per region), and `seo`.

## Module blocks

Modules register blocks via `Module::publicBlocks()` or `{Module}ServiceProvider` boot calling `PublicBlockRegistry`.

Each block declares:

- `key` — stable identifier (e.g. `webform`, `banner`)
- `label`, `icon`, `module`
- `resolver` — PHP class implementing `BlockResolver`
- `component` — Vue component key for `BlockRenderer`

Modules must **not** attach themselves to pages or theme regions. Placement is entirely in Page Manager.

## Block config schema

Each module that exposes a public block **owns its admin config UX**. Page Manager renders typed fields from the block's `configSchema()`; it does not hardcode block-specific inputs.

### Field contract

Each entry in `configSchema` is an associative array:

| Key | Required | Description |
|-----|----------|-------------|
| `key` | yes | Config key stored in `page_blocks.config` / `page_master_blocks.config` |
| `label` | yes | Admin field label |
| `type` | yes | `text`, `number`, `textarea`, or `select` |
| `default` | no | Pre-filled when a block is dropped onto a region |
| `required` | no | Marks the field as required in admin |
| `help` | no | Helper text shown under the field |
| `min` / `max` | no | Bounds for `number` fields |
| `options` | for `select` | Static list: `[['value' => 'page', 'label' => 'Page'], ...]` |
| `optionsProvider` | for `select` | Class implementing `BlockConfigOptionsProvider` — resolved at page load |

Use `optionsProvider` when choices come from the database (banners, forms, newsletter lists). Use inline `options` for small static enums.

Blocks with no settings omit `configSchema()` or pass `[]`.

### Example

```php
PublicBlock::make('featured-content')
    ->label('Featured Content')
    ->resolver(FeaturedContentBlockResolver::class)
    ->component('FeaturedContentBlock')
    ->configSchema([
        ['key' => 'heading', 'label' => 'Heading', 'type' => 'text', 'default' => 'Featured Content'],
        ['key' => 'limit', 'label' => 'Limit', 'type' => 'number', 'default' => 3, 'min' => 1, 'max' => 12],
        [
            'key' => 'content_type',
            'label' => 'Content Type',
            'type' => 'select',
            'default' => 'page',
            'optionsProvider' => ContentTypeOptionsProvider::class,
        ],
    ]),
```

At admin render time, `PublicBlockRegistry::paletteForAdmin()` resolves `optionsProvider` classes into `options` arrays for the block editor.

Resolvers read the same `config` JSON keys at public render time — schema changes do not alter the public payload contract unless the resolver is updated too.

## Theme contract

Themes declare regions in `theme.json`:

```json
"regions": [
  { "key": "hero", "label": "Hero", "description": "Full-width top area" },
  { "key": "main", "label": "Main", "description": "Primary content" },
  { "key": "footer", "label": "Footer", "description": "Above site footer" }
]
```

Public Vue uses `themes/_shared/Pages/Page/Show.vue` with `RegionShell` and `BlockRenderer`. Themes do not import module components directly.

The block editor region list always reflects the **active** public theme (`ThemeService::activeRegions()`). Block rows in the database are global — they are not duplicated per theme. When the active theme changes, [`ThemeBlockMigrationService`](../app/Services/Theme/ThemeBlockMigrationService.php) preserves blocks in matching region keys and remaps the rest (see [THEME.md](THEME.md#block-migration-on-theme-activation)).

## SEO

SEO is owned by Page Manager `Page` records via the `HasSeo` trait. The Content module no longer includes an SEO accordion.

- Page entry editor includes `seo::partials.seo-fields`
- Page Master stores default SEO fallbacks
- `SitemapService` reads published `pages`, not `contents`

## Admin permissions

- `pages.view`, `pages.create`, `pages.edit`, `pages.delete`, `pages.publish`
- `page-master.edit`

## Related

- [THEME.md](THEME.md) — theme regions and styling
- [MODULE_CONTRIBUTION.md](MODULE_CONTRIBUTION.md) — exposing public blocks from contrib modules
- [CONTENT_MODULE.md](CONTENT_MODULE.md) — legacy content admin (public role retired)
