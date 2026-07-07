# Property Listings — Post-MVP (Public Website)

Public website integration for property listings via Page Manager pages, public blocks, and a JSON API.

## Public URL format

| Page type | Path |
|-----------|------|
| City hub | `/properties/{city-slug}` |
| Listing detail | `/properties/{city-slug}/{listing-slug}` |

Eligibility: `published_to_public = true`, non-empty `city`, non-empty `slug`.

## Public integration model

- Page Manager stores generated pages under `/properties/...`
- Modules register blocks in `Module::publicBlocks()`
- Blocks resolve listing data server-side for Inertia render
- Optional JSON API in `Routes/web.php` for theme refetch

## API

**Prefix:** `api/property-listings`

| Method | Route | Purpose |
|--------|-------|---------|
| GET | `/cities/{citySlug}` | Random published listings (max 5) |
| GET | `/cities/{citySlug}/listings/{slug}` | Full listing detail DTO |

## Public blocks

| Block key | Purpose |
|-----------|---------|
| `property-listing-detail` | Full listing payload on detail pages |
| `property-listings-city` | Random 5 published listings for the page city |

City is inferred from the page URL — no admin block config required.

## Bulk page generation

**Admin → Property Listings → Configuration → Generate Public Pages**

Creates or updates Page Manager entries for all eligible listings and city hub pages, attaches the blocks above, and removes orphaned `/properties/*` pages managed by this module.

Requires a queue worker unless `QUEUE_CONNECTION=sync`.

## Vue components

Shared theme blocks:

- `themes/_shared/blocks/PropertyListingDetailBlock.vue`
- `themes/_shared/blocks/PropertyListingsCityBlock.vue`

## Implementation checklist

- [x] Add `slug` + `public_page_path` on `listings`
- [x] `PropertyListingPublicService` DTOs
- [x] `publicBlocks()` + resolvers + Vue components
- [x] `PropertyListingPageGenerationService` + bulk generator UI
- [x] Feature tests for pages, API, uninstall cleanup
- [x] `module.json` requires PageManager + SEO

See [MODULE_CONTRIBUTION.md](../../../docs/MODULE_CONTRIBUTION.md#public-blocks) for block registration rules.
