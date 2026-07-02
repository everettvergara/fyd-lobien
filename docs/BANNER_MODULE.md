# FYD CMS Banner Module

## Purpose

The Banner module is the reusable visual presentation engine for FYD CMS.
It is not limited to homepage sliders. It supports heroes, promotional
banners, CTA banners, landing page banners, section banners, sidebars,
footers, video banners, and future popup/campaign placements.

## Architecture

Banners are module-owned business records under `app/Modules/Banners`.
The module consumes framework services instead of duplicating them:

- Standard Admin List for search, filters, sorting, pagination, row actions,
  and bulk actions.
- Media Library for all image/video selection, previews, storage, and usage
  tracking.
- Policies and `{module}.{action}` permissions for authorization.
- `ActivityLogger` for administrative audit events.
- Laravel cache for public key-based rendering payloads.

Controllers remain thin. Persistence and state changes belong to
`BannerService`; public/admin preview DTOs belong to `BannerRenderingService`.

## Database

The legacy `banners` table remains the owning record and keeps compatibility
fields such as `title`, `subtitle`, `description`, and legacy media IDs.
The production structure is additive:

- `banner_templates` stores reusable template definitions and configurable
  schema/default properties.
- `banners.key` stores a unique, kebab-case identifier theme developers use
  to fetch a banner (for example `homepage-hero`, `sidebar-promo`).
- `banner_slides` stores carousel items within a single banner record.
- `banner_content_blocks` stores reusable content blocks per slide/region.
- `banner_buttons` stores multiple CTA buttons per content block.
- `banner_media_assignments` stores desktop, tablet, mobile, video, poster,
  column images, and future media slots with accessibility overrides.

Existing MVP banner rows are migrated into one slide, one content block, one
optional CTA button, and media assignment rows.

## Banner Keys

Each banner has a required, unique `key` in kebab-case. Theme developers
reference banners explicitly by key — there are no placement slots or
placement catalogs.

Sample keys seeded by `SampleContentSeeder` (one per template):

| Key | Banner |
|-----|--------|
| `sample-hero_center` | Sample: Hero Center |
| `sample-hero_left` | Sample: Hero Left |
| `sample-hero_right` | Sample: Hero Right |
| `sample-image_carousel` | Sample: Image Carousel |
| `sample-split_layout` | Sample: Split Layout |
| `sample-video_hero` | Sample: Video Hero |
| `sample-minimal` | Sample: Minimal |
| `sample-image_left` | Sample: Image Left |
| `sample-image_right` | Sample: Image Right |
| `sample-two_column_full_width` | Sample: Two-Column Full Width |
| `sample-three_column_full_width` | Sample: Three-Column Full Width |
| `sample-inner_page` | Sample: Inner Page Banner |

Themes reference banners by key. Replace sample keys with project-specific keys in Admin → Banners when building production themes.

Create new keys in Admin → Banners when adding banners for new theme regions.

## Publishing

Public visibility is controlled by **`status` only** (`draft`, `published`,
`archived`). Use admin publish / unpublish / archive actions to change status.

Only banners with `status === published` are returned by public rendering APIs.
There is no separate visibility field and no scheduled publish or expiration
dates.

## Templates

Active seeded templates:

- Hero Center, Hero Left, Hero Right
- Split Layout, Video Hero, Minimal
- Image Left, Image Right
- Image Carousel (up to 5 slides)
- Two-Column Full Width, Three-Column Full Width
- Inner Page Banner (compact page header: title, sub, teaser)

Removed templates: Hero Carousel, Fullscreen Hero, Card Overlay.

### Template field matrix

| Template | Fields |
|----------|--------|
| Hero templates | Headline, subheading, description, rich text (TinyMCE + source mode), media slots, CTA |
| Inner Page Banner | Title, sub, teaser text, optional background image. No CTAs. ~200px full-width strip. |
| Image Carousel | Up to 5 slides; each slide: image, title, subtitle, text, CTA. Empty slides are ignored on save. |
| Two-Column Full Width | Per column: picture, title, subtitle, text, CTA |
| Three-Column Full Width | Per column: picture, title, subtitle, text, CTA |

Templates expose JSON schema/default settings so the admin editor and public
renderer can derive required slides, content blocks, CTA buttons, and media
slots without hardcoded form fields.

## Media Rules

All banner media must be selected through the Media Library. Do not add direct
banner upload logic. Banner media references must be registered through
`MediaUsageService` so the Media Library can warn before deleting assets in use.

## Admin Rich Text

Hero template `rich_text` fields use the shared admin TinyMCE editor
(`x-admin.form.rich-text`). Source/HTML editing is available through the
toolbar **Code** button, matching the Content module standard.

Column template **Text** fields remain plain textareas.

Changing the template in the admin editor reloads the form so slide and column
fields match the selected template schema immediately.

The banner editor color-codes block components for easier editing: main content
blocks, column blocks, slide media, per-block media pickers, and CTA sections
each use distinct tinted panels with type badges.

## Rendering

Use `BannerRenderingService` or the `PublicContent` facade for public payloads:

```php
// Theme developer picks up a banner by stable key
app(BannerRenderingService::class)->bannerByKey('homepage-hero');
PublicContent::bannerByKey('sidebar-promo');
```

Returns `null` when no published banner exists for the key. Draft and archived
banners are excluded.

Public Vue rendering uses `resources/js/Components/BannerRenderer.vue` and
`resources/js/Components/Carousel.vue`. Column templates render each block as
an independent column with picture, title, subtitle, text, and CTA. Carousel
components iterate `banner.slides[]` within a single banner record.

## Permissions

Banner permissions follow the standard format:

- `banners.view`
- `banners.create`
- `banners.edit`
- `banners.delete`
- `banners.publish`
- `banners.archive`

Users also need `media.view` to use the Media Library picker.

## Future Extension Points

The schema is prepared for:

- Popup banners
- Campaign groups
- A/B testing
- Personalized or targeted banners
- Language, country, role, and device targeting
- Analytics events for impressions, clicks, CTR, and conversions
- AI-generated variants
