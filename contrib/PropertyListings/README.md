# Property Listings Module

Installable business module for property/building listings, units, fees, assets, and admin-managed dropdown lookups.

**Scope:** MVP is **admin portal only**. Public website integration (Page Manager blocks + JSON API) is **post-MVP** — block specs will be provided separately.

## Documentation in this folder

| Document | Purpose |
|----------|---------|
| [docs/DATABASE.md](docs/DATABASE.md) | Tables, relationships, lookup groups, field reference |
| [docs/ADMIN.md](docs/ADMIN.md) | Admin UI layout, filtering, comparator, import/export, assets |
| [docs/POST-MVP.md](docs/POST-MVP.md) | Public blocks and API (deferred) |

Repository-level docs:

- [docs/MODULE_CONTRIBUTION.md](../../docs/MODULE_CONTRIBUTION.md) — contrib module standards  
- [docs/MODULE_LIFECYCLE.md](../../docs/MODULE_LIFECYCLE.md) — install / uninstall  
- [docs/PROPERTY_LISTING_CONTRIB_DB.md](../../docs/PROPERTY_LISTING_CONTRIB_DB.md) — legacy draft pointer  

## MVP features (admin)

| Feature | Summary |
|---------|---------|
| **Listings CRUD** | Full data entry: header cards, 1:1 sections, Units/Fees/Assets tabs, remarks sidebar |
| **Dropdown Values** | Admin-managed `listing_lookups` per group (property type, grade, asset types, etc.) |
| **List index** | Table + thumbnail views; comprehensive listing + unit filters |
| **Comparator** | Bin (max 5); click bin icon → compare page; add/remove from table or thumbnails |
| **CSV import/export** | Listing header + units; filtered export; import preview |
| **Batch assets** | `{code}__{asset_type}.{ext}` filename matching; replace same type per listing |
| **Image optimization** | Raster assets resized (max 1920px), JPEG 75% before Media storage |

## Post-MVP (not in initial release)

- Page Manager `publicBlocks()` for public website  
- `api/property-listings/*` JSON API for block hydration  
- Additional public blocks (search hero, links, agent consult, etc.) — TBD  

## Install

```bash
# Windows (PowerShell)
Copy-Item -Recurse contrib\PropertyListings app\Modules\PropertyListings

# Linux / macOS
cp -r contrib/PropertyListings app/Modules/PropertyListings
```

1. **Administration → Modules** → Rescan → Install **Property Listings**  
2. **Administration → Roles** → assign permissions (`listings.*`, `listings.lookups.*`)  

For active development, symlink instead of copy:

```bash
ln -s ../../contrib/PropertyListings app/Modules/PropertyListings
```

## Requires core modules

- **Address** — province/city selects  
- **Media** — listing assets  
- **AuditLogs** — activity logging  

PageManager and SEO required when public blocks ship (post-MVP).

## Development

Author all module source under `contrib/PropertyListings/`. Namespace: `App\Modules\PropertyListings\`.

```text
contrib/PropertyListings/
├── README.md                 ← this file
├── module.json
├── Module.php
├── docs/
│   ├── DATABASE.md
│   ├── ADMIN.md
│   └── POST-MVP.md
├── Controllers/
├── Models/
├── Services/
├── Policies/
├── Requests/
├── Routes/
│   └── admin.php             ← MVP: admin only (no web.php)
├── Views/
└── Database/
    ├── Migrations/
    └── Seeders/
```

## Guidelines for contributors

1. Follow [MODULE_CONTRIBUTION.md](../../docs/MODULE_CONTRIBUTION.md) strict layout.  
2. Business models stay in this module — not `app/Models/`.  
3. Register permissions and menus only in `Module.php` — no kernel edits.  
4. All migrations must implement `down()`.  
5. Dropdown options come from `listing_lookups` — not hardcoded enums in forms.  
6. Admin list uses Standard Admin List services; row actions inline (no dropdown menus).  
7. Do not auto-attach Page Manager blocks on install.  
8. Update `docs/DATABASE.md` and `docs/ADMIN.md` when schema or admin behavior changes.
