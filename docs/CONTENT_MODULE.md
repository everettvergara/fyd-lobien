# FYD CMS Content Module

## Purpose

The Content module is the unified CMS content store for FYD CMS. It replaces
the former separate Pages and Posts modules with a single `contents` table
and a **content type master registry** for classification.

Supported content types at launch:

- `page` — static pages (About, Services, Legal)
- `article` — blog posts and news articles (merged from Posts)

## Content Type Master Registry

Content types are the master classification for all content entries. They are stored in the **`content_types`** database table (admin-managed) and bootstrapped from [`config/content-types.php`](../config/content-types.php) on migration.

### Admin: Content Types (`/admin/content-types`)

| Capability | Details |
|------------|---------|
| List | Standard admin list — key, label, icon, description, entry count, active status, sort order |
| Create / Edit | Key (immutable after create), label, description, Bootstrap icon class, sort order, active toggle |
| Add Content | Row action links to content create form with type pre-selected |
| Delete | Allowed only when no content entries use the type |

Sidebar: **Content → Content Types**

Registry service: `App\Support\ContentTypeRegistry` (reads active types from DB, cached)

| Method | Purpose |
|--------|---------|
| `all()` | Active types keyed by `key` |
| `keys()` | Allowed type keys for validation |
| `label(string $key)` | Display label |
| `icon(string $key)` | Bootstrap Icons class for badges and sidebar |
| `badgeHtml(string $key)` | Admin list type badge markup |
| `options()` | `[key => label]` for admin select |
| `forgetCache()` | Clear cache after type changes |

Every content entry requires a `content_type` validated against active registry keys.

`config/content-types.php` remains the default seed source for fresh installs. Use **Content Types** in admin to add or edit types without deploying code changes.

## Data Model

Table: `contents`

| Column | Notes |
|--------|-------|
| `content_type` | Required, indexed; from master registry |
| `title`, `slug`, `summary` | Core metadata |
| `body` | Rich HTML (formerly `pages.content`) |
| `featured_image_id` | Media Library FK |
| `status`, `published_at`, `author_id` | Publishing |

SEO metadata uses the shared `HasSeo` trait (polymorphic `seo_meta`).

### Removed (do not reintroduce)

- `posts` table and Posts module
- `page_sections` table and section builder
- `template` and `parent_id` columns (replaced by `content_type`)

## Admin

| Item | Value |
|------|-------|
| Routes | `admin.content.*` |
| URL prefix | `/admin/content` |
| Permissions | `content.view`, `content.create`, `content.edit`, `content.delete`, `content.publish` |
| Policy | `ContentPolicy` |
| List service | `ContentAdminListService` |

### Admin sidebar (Content section)

Single sidebar entry:

| Menu label | URL | Notes |
|------------|-----|-------|
| Content Management | `/admin/content` | Active on all `admin.content.*` routes |

Type filtering is available via the list toolbar **Type** dropdown, not separate sidebar links.

### Content list

Columns (in order): No, ID, Title, **Type**, URI, Status, Author, Published, Updated.

- **Type** — badge with registry icon + label; always visible (including filtered views); sortable by `content_type`
- **Type filter** — toolbar dropdown (All / each registry type); uses `?content_type=` query param; invalid values are ignored

When filtered by type, the page title uses the pluralized type label (e.g. “Articles”) and **Add Content** links to create with that type pre-selected.

### Create / edit form layout

Two-column layout on desktop (`col-lg-8` / `col-lg-4`):

**Left column**

1. Title
2. Slug
3. Summary
4. Body (rich text editor)

**Right column (collapsible cards, collapsed by default)**

1. **Publishing** — content type, status
2. **Featured Image** — single-select media picker
3. **SEO Settings** — fields only (no nested accordion)

Sections auto-expand when their fields have validation errors.

Body HTML is sanitized on save via `App\Support\HtmlSanitizer`.

### Body editor

- **Visual mode** — TinyMCE (lists, links, formatting, image insert)
- **Source mode** — CodeMirror 6 HTML editor with syntax highlighting and line numbers
- Toggle syncs content between modes; form submit saves the active editor content
- **Image button** — opens the media picker in multi-select mode; upload and insert multiple images into the body
- Featured image and SEO OG image pickers remain single-select, but their upload control accepts multiple files (all are saved to the library)

## Public Site

| Item | Value |
|------|-------|
| Route | `GET /{slug}` → `content.show` |
| Controller | `App\Http\Controllers\Public\ContentController` |
| Inertia page | `resources/js/Pages/Content/Show.vue` |
| DTO | `PublicContent::entry()`, `PublicContent::contentCard()` |

All content types render at `/{slug}`. The public DTO includes
`contentType: { key, label }` for future per-type layouts.

Homepage:

- `featuredContent` — published entries with `content_type = page`
- `latestArticles` — published entries with `content_type = article`

Search uses `ContentSearchService` against the unified `contents` table and
returns a type label from the registry.

## Migrations

Fresh installs:

1. `2026_07_01_200004_create_pages_table.php` — legacy bootstrap (creates `pages`)
2. `2026_07_02_200000_consolidate_content_module.php` — renames to `contents`, adds `content_type`, renames `content` → `body`, drops sections/posts

The consolidation migration is irreversible. **Back up your database before running migrations** on environments with live content.

If upgrading from Pages/Posts modules, the migration renames `pages` → `contents`, migrates `posts` rows into `contents` as `content_type = article`, then drops legacy tables. Running `php artisan db:seed --class=Database\\Seeders\\SampleContentSeeder` on a database with real content can overwrite entries that share sample slugs (e.g. `about`, `services`). See [SEEDING.md](SEEDING.md).

## Activity and Media Usage

- Activity log module key: `content`
- Media usage module key: `content`

```php
ActivityLogger::log('content', 'created', $content, ['title' => $content->title]);

app(MediaUsageService::class)->syncModel($content, 'content', [
    'featured_image_id' => 'Featured Image',
]);
```

## Related Documentation

- [DEVELOPMENT.md](DEVELOPMENT.md) — day-to-day development
- [MEDIA_LIBRARY.md](MEDIA_LIBRARY.md) — featured image and OG image pickers
- [MODULE_STANDARD.md](MODULE_STANDARD.md) — module structure
