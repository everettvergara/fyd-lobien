# Property Listing — Database Schema (legacy draft)

> **Canonical documentation moved to:** [contrib/PropertyListings/docs/DATABASE.md](../contrib/PropertyListings/docs/DATABASE.md)

Use the module docs for the current schema. Key corrections from the original draft:

- **`listing_type` removed** — use `listing_units.for_lease` / `for_sale`
- **Second `handover_condition` block is `availability`** (Vacant / Leased)
- **`listing_assets.media_id`** replaces `asset_path` (Media module)
- **Dropdown values** live in `listing_lookups` table (admin-managed)

Module overview: [contrib/PropertyListings/README.md](../contrib/PropertyListings/README.md)
