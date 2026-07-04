# Property Listings — Post-MVP (Public Website)

The public website does **not** ship in the admin MVP. When ready, this module will expose Page Manager blocks and a supporting JSON API.

## Public integration model

- Themes render blocks via `themes/_shared/blocks/` and `BlockRenderer`  
- Modules register blocks in `Module::publicBlocks()`  
- Blocks fetch data from JSON API routes in `Routes/web.php`  
- Admins compose pages in Page Manager — no hardcoded public routes required  

## Planned API (not implemented in MVP)

**Prefix:** `api/property-listings`

| Method | Route | Purpose |
|--------|-------|---------|
| GET | `/search` | Paginated search |
| GET | `/{code}` | Listing detail DTO |
| GET | `/options` | Filter dropdown values |

## Planned blocks (specs TBD)

Placeholder keys discussed — **confirm with product before implementing:**

| Block key | Purpose |
|-----------|---------|
| `property-search` | Search hero / filter form |
| `property-listings` | Results grid |

Additional blocks (user to specify later): property links, categories grid, agent consult, featured listings, public comparator, etc.

## Implementation checklist (when starting post-MVP)

- [ ] Add `Routes/web.php` with API routes  
- [ ] `PropertyListingPublicService` DTOs  
- [ ] `publicBlocks()` + resolvers + `configSchema()`  
- [ ] Shared Vue components in `themes/_shared/blocks/`  
- [ ] Feature test: uninstall removes block placements  
- [ ] Update `module.json` `requires_core` to include PageManager, SEO  

See [MODULE_CONTRIBUTION.md](../../../docs/MODULE_CONTRIBUTION.md#public-blocks) for block registration rules.
