# FYD CMS Content Blocks Module

## Purpose

The **Content Blocks** module aggregates entries from the Content module and exposes saved query/display definitions as blocks for **Page Master** and **Pages**.

Admin users configure Drupal Views–style definitions:

- Content type selection
- Fields to retrieve and display
- Filters based on field values
- Sort order, item limit, and optional pagination
- Display formatter: unformatted, table, ordered list, unordered list
- Default CSS class and HTML id hooks on each field for theme styling

Page Manager placement uses a single PublicBlock type (`content-block`) that references a saved definition by **key**.

## Relationship to other modules

| Module | Role |
|--------|------|
| **Content** | Source data (`contents` table) |
| **Page Manager** | Block placement on public pages |
| **Banners** | Unrelated visual marketing banners |

The former Page Manager blocks `featured-content` and `latest-articles` were removed in favor of seeded Content Block definitions (`featured-pages`, `latest-articles`).

## Data model

Table: `content_blocks`

| Column | Notes |
|--------|-------|
| `key` | Unique kebab-case identifier |
| `name` | Admin label |
| `icon` | Bootstrap Icons class (`ContentBlockSeeder::MENU_ICON` for module menu; per-block icons seeded for samples) |
| `status` | `draft`, `published`, `archived` |
| `content_types` | JSON array of content type keys |
| `fields` | JSON field display config |
| `filters` | JSON filter rows |
| `sort_field`, `sort_direction` | Query sort |
| `items_per_page` | Result limit / page size |
| `pagination_enabled` | Public pagination toggle |
| `formatter` | `unformatted`, `table`, `ol`, `ul` |
| `wrapper_class`, `wrapper_id` | Optional wrapper overrides |

### Field config JSON

```json
{
  "field": "title",
  "label": "Title",
  "class": "content-block__title",
  "id": "content-block-latest-articles-title",
  "sort_order": 0
}
```

### Filter config JSON

```json
{
  "field": "title",
  "operator": "contains",
  "value": "News",
  "group": "and"
}
```

## Field registry

Queryable/displayable fields (`ContentBlockFieldRegistry`):

| Field key | Type |
|-----------|------|
| `title`, `slug`, `summary` | text |
| `body` | html (sanitized on render) |
| `content_type` | content type label |
| `published_at` | date |
| `author.name` | relation text |
| `featured_image` | media `{ url, alt }` |

## Admin

| Item | Value |
|------|-------|
| Routes | `admin.content-blocks.*` |
| URL | `/admin/content-blocks` |
| Permissions | `content_blocks.view`, `.create`, `.edit`, `.delete`, `.publish`, `.archive` |
| Sidebar | **Content → Content Blocks** (`bi-view-stacked` via `ContentBlockSeeder::MENU_ICON`) |

Search fields: `name`, `key`

### Preview / Retrieve

The create/edit form includes full-width **Preview / Retrieve** and **Generated SQL** accordion panels at the bottom of the page (light gray headers with bold black titles, gray section bodies).

- Click **Retrieve** to run the current configuration against published content without saving.
- The edit screen loads an initial preview (and SQL) automatically.
- **Auto Update Preview on save** — when checked, the preview refreshes after you save and remain on the edit screen. When unchecked, save returns to the edit screen without running the preview query.
- **Show SQL preview** — when checked, reveals the Generated SQL panel with count and data queries.

Preview uses the selected formatter and reports total matching items vs. rows returned (respecting items per page and pagination). Generated SQL shows the count query and the data query (sort, limit, and offset when paginated) without eager-load queries.

Create and update actions redirect back to the edit screen instead of the index list.

## Public rendering

```php
PublicContent::contentBlockByKey('latest-articles');
PublicContent::contentBlockByKey('featured-pages', page: 2);
```

Page Manager block config:

```json
{ "content_block_key": "latest-articles" }
```

Pagination query param: `cb_{key}_page` (example: `cb_latest-articles_page=2`)

### Theme CSS hooks

Default class naming:

```text
content-block content-block--{key}
content-block__format content-block__format--{formatter}
content-block__item
content-block__row
content-block__field content-block__{field}
```

Vue renderer: `themes/_shared/blocks/ContentBlockBlock.vue`

## Seeded definitions

| Key | Replaces | Summary |
|-----|----------|---------|
| `latest-articles` | Page Manager `latest-articles` | Articles, 3 items, unformatted |
| `featured-pages` | Page Manager `featured-content` | Pages, 3 items, unformatted |

## Activity logging

Module key: `content_blocks`

## Related documentation

- [CONTENT_MODULE.md](CONTENT_MODULE.md)
- [PAGE_MANAGER.md](PAGE_MANAGER.md)
- [MODULE_STANDARD.md](MODULE_STANDARD.md)
