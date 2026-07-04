# Property Listings — Admin Guidelines

All features below are **admin portal only** for MVP. The public website uses Page Manager blocks (post-MVP).

## Admin menu

| Menu item | Route prefix | Purpose |
|-----------|--------------|---------|
| **Listings** | `admin/listings` | CRUD, import/export, batch assets, list views, comparator |
| **Dropdown Values** | `admin/listing-lookups` | Manage `listing_lookups` option sets |
| **Configuration** | `admin/listings/configuration` | Module settings; manual sample listing seed |

## Configuration

- **Dropdown values** are seeded automatically on `module:install PropertyListings --force` (lookups only).
- **Property listings** are never seeded on install.
- Use **Seed Sample Listings** on the Configuration page to create or refresh five demo listings (`DEMO-001` … `DEMO-005`) with full form data, units, fees, remarks, and image assets.
- Requires `listings.configuration.manage` permission.

## Listing data entry (create / edit)

**Layout:** three-column form grid — left (Identity, Location, Building Services), middle (Listing Specs, Rates & Sizing, Other Services), right (remarks sidebar). Remarks can be hidden so the two form columns expand to full width; use the floating expand button on the right edge to restore.

### Header cards (listing core)

1. **Identity** — code*, name*, completion_status  
2. **Location** — province*, city*, brgy, address (Address module selects where possible)  
3. **Rates & sizing** — office_rental_rate, total_area_size, unit_market_size, retail_market_rate  

### Stacked cards (1:1 — not tabs)

4. **Listing Specs** — developer, grade, completion year/qtr, floors, areas, efficiency; **net_usable_area** shown computed only  
5. **Building Services** — operating hours, AC, lifts, telco, backup power  
6. **Other Info** — PEZA, sustainability, other_info_visible  

### Tabs (1:many only)

| Tab | Content |
|-----|---------|
| **Units** | Compact `table table-sm` with drag-sort; hidden `sort_order` |
| **Fees** | `table table-sm` with drag-sort; hidden `sort_order` |
| **Assets** | Bulk upload-by-type pane (top) + sortable assets table (bottom) |

Tab labels and pane outlines are color-coded: Units (blue), Fees (amber), Assets (teal).

### Assets tab

1. **Bulk upload pane** — one file input per `image_type` lookup row; POST preview/commit on the listing edit screen. Extension validated per `file_kind`. Uploading a type replaces an existing asset of that type.
2. **Assets list** — sortable table of current assets; drag to reorder; Save Listing persists `sort_order`. Remove row + Save deletes the asset.

On **create**, the upload pane is disabled until the listing is saved.

**Global batch** (list toolbar → Batch Upload Assets) still accepts `{code}__{asset_type_slug}.{ext}` filenames for multi-listing uploads.

### Remarks sidebar (not a tab)

- Chronological feed (user, date, comment)  
- Optional unit filter  
- Quick-add form  
- On create: enabled after first save (or AJAX once listing exists)
- Hide via header chevron; form columns expand to full width when hidden

## List index

### View modes

- **Table** (default) — standard admin list  
- **Thumbnails** — card grid with building image, same summary fields  

Toggle persists in `sessionStorage` (`listings_view`).

### Filtering

**Search:** code, name, address, province, city, developer  

**Listing filters:** province, city, completion_status, grade, developer  

**Unit filters:** property_type, availability, handover, bedrooms, for_lease, for_sale, rental range, area range, floor  

### Comparator bin (max 5)

| Action | Behavior |
|--------|----------|
| **Compare** on table row or thumbnail | Add/remove from bin |
| **Click bin icon or label** | Navigate to `/admin/listings/compare?ids=…` |
| **× on chip / Clear all** | Remove from bin |

Compare page shows side-by-side matrix when ≥2 listings; otherwise prompts to add more.

State: `sessionStorage` key `fyd-listing-comparator`.

### Import / export (list toolbar)

| Action | Description |
|--------|-------------|
| **Download CSV** | Exports filtered results; flat rows (header + unit columns per row) |
| **Download Template** | Empty CSV with column headers |
| **Upload CSV** | Preview → confirm; upsert by `code`; multiple rows = multiple units |
| **Batch Upload Assets** | Multi-file or ZIP; filename `{code}__{asset_type_slug}.{ext}` |

**CSV scope:** listing header + units only. Fees via admin UI. Assets via batch or Assets tab.

**Batch asset replace rule:** same listing + asset_type replaces existing asset.

## Assets — upload rules

| file_kind | Extensions | Processing |
|-----------|------------|------------|
| image | jpg, jpeg, png, gif, webp, bmp (+ svg stored as-is) | Resize max 1920px long edge; JPEG quality **75%** |
| pdf | pdf | No processing |

Validation uses `listing_lookups.meta.file_kind` for the selected asset type.

## Permissions

| Permission | Capability |
|------------|------------|
| `listings.view` | View listings, compare page |
| `listings.create` | Create |
| `listings.edit` | Edit |
| `listings.delete` | Delete |
| `listings.export` | Download CSV |
| `listings.import` | Upload CSV / template |
| `listings.assets.batch` | Batch asset upload |
| `listings.lookups.view` | View dropdown hub |
| `listings.lookups.create` | Add lookup values |
| `listings.lookups.edit` | Edit lookup values |
| `listings.lookups.delete` | Delete unused lookup values |
| `listings.configuration.manage` | Configuration page + seed sample listings |
