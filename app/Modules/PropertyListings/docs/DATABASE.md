# Property Listings — Database Schema

Authoritative schema for the Property Listings business module. Dropdown values are stored in `listing_lookups` and managed via **Administration → Property Listings → Dropdown Values**.

## Lookup groups (`listing_lookups`)

| Group key | Used on | Notes |
|-----------|---------|-------|
| `image_type` | `listing_assets.asset_type` | `meta.file_kind`: `image` or `pdf` |
| `property_type` | `listing_units.property_type` | Optional summary, description, image for public cards |
| `completion_status` | `listings.completion_status` | Existing, Pipeline |
| `property_use` | Filters / future use | |
| `handover_condition` | `listing_units.handover_condition` | Fit-out options |
| `availability` | `listing_units.availability` | Vacant, Leased |
| `bedrooms` | `listing_units.bedrooms` | |
| `grade` | `listing_specs.grade` | |
| `fee_type` | `listing_fees.fee_type` | |
| `peza_accreditation` | `listing_other_infos.peza_accreditation` | |

### Default `image_type` values

| value | label | file_kind |
|-------|-------|-----------|
| `building` | Building | image |
| `floor-plan` | Floor Plan | image |
| `map` | Map | image |
| `interior` | Interior | image |
| `flyers` | flyers | pdf |

### Default `property_type` values

Commercial - Office Use, Commercial - Retail Use, Commercial - Others, Residential - Condo, Residential - House and Lot, Residential - Others, Industrial - Warehouse, Industrial - Others, Lot, Others (labels include category in parentheses per seed data).

### Default `handover_condition` values

Bare Shell, Warm Shell, Partially Fitted, Fully Fitted, As-is-where-is

### Default `availability` values

Vacant, Leased

### Default `bedrooms` values

Studio, 1BR, 2BR, 3BR, Others

### Default `grade` values

A+ (Prime), A, B, C

### Default `fee_type` values

Rental Rate, Dues/CUSA, Parking Fee

### Default `completion_status` values

Existing, Pipeline

### Default `property_use` values

Commercial, Residential, Industrial, Others

### Default `peza_accreditation` values

| value | label |
|-------|-------|
| `yes` | Yes |
| `no` | No |
| `processing` | Processing |

---

## Demo listings (manual seed only)

Demo property listings are **not** created on module install. Seed them from **Admin → Property Listings → Configuration → Seed Sample Listings**.

| Code | Purpose |
|------|---------|
| `DEMO-001` … `DEMO-005` | Full listing records with specs, building services, other info, 3 units, 3 fees, 2 remarks, 4 image assets each |

Re-running the seed action refreshes demo rows by `code` (idempotent). Requires admin user `admin@fyd.local` for remarks and media assets.

---

## Tables

### `listings`

| Column | Type | Required |
|--------|------|----------|
| id | PK | |
| code | string, unique | yes |
| name | string | yes |
| province | string, nullable | |
| city | string, nullable | |
| brgy | string | |
| address | text | |
| office_rental_rate | decimal | |
| total_area_size | decimal | |
| unit_market_size | decimal | |
| retail_market_rate | decimal | |
| completion_status | string (lookup) | |
| published_to_public | boolean, default false | |

**Note:** `listing_type` was removed. Lease/sale intent is on units via `for_lease` / `for_sale`.

### `listing_specs` (1:1)

developer, grade, completion_year, completion_qtr, no_of_floors, no_of_basement, density_ratio (text ratio, e.g. `1:450`), parking_allocation, floor_to_ceiling_height, gross_leasable_area, typical_floor_area, typical_retail_floor_area, floor_efficiency (text, e.g. `85% efficient`)

**Computed (not stored):** `net_usable_area` = `typical_retail_floor_area × floor_efficiency` only when `floor_efficiency` is numeric

### `listing_building_services` (1:1)

operating_hours, ac_system, no_of_lifts_passenger (text), no_of_lifts_service (text), telco, backup_power (text)

### `listing_other_infos` (1:1)

peza_accreditation, sustainability, other_info_visible (bool, default true)

### `listing_units` (1:many)

floor, unit, area_size, rental, handover_condition, **availability**, bedrooms, selling_price, property_type, for_lease, for_sale, last_remarks, sort_order

### `listing_remarks` (1:many)

listing_unit_id (nullable), user_id, comment, remarked_at

### `listing_fees` (1:many)

fee_type, fee, sort_order

### `listing_assets` (1:many)

asset_type, **media_id** (FK → media), sort_order

**Note:** Replaces draft `asset_path`. Files live in the Media library.

---

## Entity relationships

```text
listings
├── listing_specs (1:1)
├── listing_building_services (1:1)
├── listing_other_infos (1:1)
├── listing_units (1:many)
│   └── listing_remarks (optional scope)
├── listing_fees (1:many)
├── listing_assets (1:many)
└── listing_remarks (listing-level, unit_id null)
```
