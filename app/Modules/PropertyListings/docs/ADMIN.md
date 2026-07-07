# Property Listings — Admin Guidelines

All features below are **admin portal** unless noted. The public website uses generated Page Manager pages and public blocks — see [POST-MVP.md](POST-MVP.md).

## Admin menu

| Menu item | Route prefix | Purpose |
|-----------|--------------|---------|
| **Listings** | `admin/listings` | CRUD, list views, filters, comparator |
| **Property Uploaders** | `admin/property-uploaders` | CSV templates/import/export for headers, units, fees; bulk assets |
| **Dropdown Values** | `admin/listing-lookups` | Manage `listing_lookups` option sets |
| **Configuration** | `admin/listings/configuration` | Module settings; sample seed; **bulk public page generation** |

## Configuration

- **Dropdown values** are seeded automatically on `module:install PropertyListings --force` (lookups only).
- **Property listings** are never seeded on install.
- Use **Seed Sample Listings** on the Configuration page to create or refresh five demo listings (`DEMO-001` … `DEMO-005`) with full form data, units, fees, remarks, and image assets.
- Use **Generate Public Website** to create/update the `/properties` hub page (city cards + search banner), the `/properties/search` results page, city hub pages, listing pages for all listings with **Publish to PUBLIC** enabled (requires city + slug), and the **Properties** footer menu. Shows a progress bar while the queue job runs.
- Use **Clear Public Website** to remove all generated pages, their blocks, and the Properties footer menu entries.
- Requires `listings.configuration.manage` permission.

## Listing slug (public URL)

- **Slug** field on the listing form (auto-generated from name on save if empty).
- Public URL preview: `/properties/{city-slug}/{slug}`
- Unique per city (same slug allowed in different cities).
- Required when **Publish to PUBLIC** is enabled.

## Listing data entry (create / edit)

**Layout:** three-column form grid — left (Identity, Location, Building Services), middle (Listing Specs, Rates & Sizing, Other Services), right (remarks sidebar). Remarks can be hidden so the two form columns expand to full width; use the floating expand button on the right edge to restore.

### Header cards (listing core)

1. **Identity** — code*, name*, summary (plain text, max 500 chars), description (rich HTML with Visual/Source editor), slug (public URL segment), completion_status, **published_to_public** (Publish to PUBLIC)  
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

**Global batch** (Property Uploaders → Assets Uploader) requires selecting one asset type first, then accepts `{code}__{whatever_text}.{ext}` filenames for multi-listing uploads.

### Remarks sidebar (not a tab)

- Chronological feed (user, date, comment)  
- Optional unit filter  
- Quick-add form  
- Users with `listings.edit` can delete remarks via the red trash icon on each card (confirm dialog)  
- On create: enabled after first save (or AJAX once listing exists)
- Hide via header chevron; form columns expand to full width when hidden

## List index

### View modes

- **Table** (default) — standard admin list; search/filter toolbar uses `admin-list-toolbar` styling; toolbar and table share one card  
- **Thumbnails** — card grid with building images from `building` assets; click image or title to open edit; multiple building assets show a carousel  

Table and thumbnail views show per listing:

- **Property Types** — distinct unit property types (lookup labels)
- **AVL** — unit availability counts (e.g. Vacant (2), Leased (1))
- **Lease / Sale** — badges when any unit is for lease and/or for sale
- **Public** — inline toggle for `published_to_public` (users with `listings.update`); PATCH saves immediately

**Bulk publish actions** (users with `listings.edit`, page header):

- **Publish all to public** — sets `published_to_public = true` on every listing in the database
- **Unpublish all from public** — sets `published_to_public = false` on every listing

These do not generate or remove public pages; use **Configuration → Generate Public Website** / **Clear Public Website** for page sync.

Toggle persists in `sessionStorage` (`listings_view`).

### Filtering

Search/filter toolbar matches standard admin list background (`admin-list-toolbar`).

**Search:** code, name, address, province, city, developer  

**Listing filters:** province, city, completion_status, grade, developer  

**Unit filters:** property_type, availability, handover, bedrooms, for_lease, for_sale, rental range, area range, floor  

### Comparator bin (max 5)

| Action | Behavior |
|--------|----------|
| **Compare** on table row or thumbnail | Add/remove from bin |
| **Compare bin** | Inline strip below filters, above table/grid (hidden when empty) |
| **Click bin Compare** | Navigate to `/admin/listings/compare?ids=…` |
| **× on chip / Clear all** | Remove from bin |

Compare page (≥2 listings) section order:

0. **Unit filter** — toolbar above compare table; client-side filter for units row only (hidden when printing)
1. **Building** — image carousel per listing column (`asset_type = building`)
2. **Floor plan** — image carousel per listing column (`asset_type = floor-plan`)
3. **Location** — province, city, barangay, address per column
4. **Units** — read-only unit table per column (floor, unit, area, rent, HO, availability, etc.)
5. **Fees** — read-only fee table per column (fee type, amount)
6. **Field matrix** — identity, rates, specs, building services
7. **Disclaimer** — standard leasing disclaimer footer

| Feature | Behavior |
|---------|----------|
| **Click carousel image** | Full-size popup preview (building or floor plan) |
| **Print** | Print button or Ctrl+P; landscape layout; unit filter excluded; filtered unit rows + carousel images + fees + disclaimer included |
| **Compare bin on compare page** | Fixed bottom bar (hidden when printing) |

Compare page prompts to add more when fewer than two listings are selected.

State: `sessionStorage` key `fyd-listing-comparator`.

### Property Uploaders

Uploader actions live on **Property Uploaders**, not on the Listings index toolbar.

| Uploader | Template/export/import behavior | Upsert key |
|----------|---------------------------------|------------|
| **Property Header** | Listing-level fields only: `listings` + specs + building services + other info | `code` |
| **Property Units** | Unit rows linked to parent listing by `code` | `code` + `floor` + `unit` |
| **Property Fees** | Fee rows linked to parent listing by `code` | `code` + `fee_type` |

Each CSV has:

- **Template** — empty CSV with the prescribed columns
- **Export Existing** — exports current data for the selected CSV type
- **Upload** — preview → confirm; inserts new rows and updates matching rows

CSV uploads are full-batch validated before commit:

- Uploaded columns must exactly match the selected template.
- Required fields must be filled. Property Header requires `code` and `name`; `province` and `city` may be blank.
- Dropdown-backed fields should use maintained `listing_lookups.value` codes, not labels.
- Unknown dropdown codes are reported as warnings, marked with `*` in preview, and imported as blank values.
- Unit rows with unknown parent listing `code` values are reported as warnings and ignored.
- Fees reject missing parent listing `code` values.
- Missing rows in an uploaded CSV do **not** delete existing units or fees.

### Assets Uploader

Bulk asset uploading also lives on **Property Uploaders**.

1. Select the asset type for the whole batch (for example `flyers`).
2. Select individual files or upload a ZIP. Individual multi-select uploads are staged one file at a time so per-file errors are visible.
3. Name each file `{code}__{whatever_text}.{ext}`.

Only the filename text before `__` is used as the listing `code`; the text after `__` is descriptive. The selected asset type applies to every file in the batch.

**Batch asset replace rule:** same listing + selected asset_type replaces existing asset.

## Assets — upload rules

| file_kind | Extensions | Processing |
|-----------|------------|------------|
| image | jpg, jpeg, png, gif, webp, bmp (+ svg stored as-is) | Resize max 1920px long edge; JPEG quality **75%** |
| pdf | pdf | No processing |

Validation uses `listing_lookups.meta.file_kind` for the selected asset type.

## Brochures (print preview)

Each listing supports six browser-printable brochure previews:

| Type | Route suffix | Content |
|------|--------------|---------|
| Interior | `interior` | Interior asset images |
| Property photos | `property-photos` | Building asset images |
| Floor plan | `floor-plan` | Floor plan asset images |
| Floors / units | `floors-units` | Units table, fees, disclaimer |
| Property information | `property-information` | Specs, building + map images |
| Print all | `all` | All sections combined |

Routes:

- Hub: `GET /admin/listings/{listing}/brochures`
- Preview: `GET /admin/listings/{listing}/brochures/{type}`

**Shortcuts:** icon buttons on the listings table/thumbnail actions and the edit page header open each preview in a new tab. Each preview page includes a **Print** button; printing outputs only the branded template (logo hexagon, property name banner, copyright footer) plus that brochure's content.

**Header branding:** the hexagon is a fixed PNG frame (`public/modules/property-listings/brochure-hexagon-frame.png`, transparent center) layered over the site logo from **Settings → Site Logo** (`general.site_logo_id`). The navy ribbon's height equals one hexagon side and joins the hexagon's right edge seamlessly. Copyright in the footer uses **Settings → Website Name** (`general.website_name`).

Uses `listings.view` permission.

## Permissions

| Permission | Capability |
|------------|------------|
| `listings.view` | View listings, compare page, brochure previews |
| `listings.create` | Create |
| `listings.edit` | Edit |
| `listings.delete` | Delete |
| `listings.export` | Export CSVs from Property Uploaders |
| `listings.import` | Upload CSVs / download templates from Property Uploaders |
| `listings.assets.batch` | Bulk asset upload from Property Uploaders |
| `listings.lookups.view` | View dropdown hub |
| `listings.lookups.create` | Add lookup values |
| `listings.lookups.edit` | Edit lookup values |
| `listings.lookups.delete` | Delete unused lookup values |
| `listings.configuration.manage` | Configuration page + seed sample listings |
