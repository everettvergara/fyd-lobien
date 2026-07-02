# ADR 001: Model Placement

## Status

Accepted

## Context

FYD CMS modules own domain tables and business logic, but several models
live in `app/Models/` while others live under `app/Modules/{Module}/Models/`.
During Framework Stabilization we need a consistent rule so module authors
know where to place new models without breaking existing code.

Current split (MVP):

| Location | Examples |
|----------|----------|
| `app/Models/` | User, Role, Permission, Setting, Media, MediaFolder, SeoMeta, ActivityLog |
| `app/Modules/*/Models/` | Content, Banner, Menu, MenuItem |

## Decision

### Framework / kernel models → `app/Models/`

Models that serve multiple modules or framework services remain in
`app/Models/`:

- Identity and access: User, Role, Permission
- Cross-cutting infrastructure: Setting, Media, MediaFolder, SeoMeta, ActivityLog
- Future framework models (e.g. LoginHistory, NotificationPreference)

These are shared kernel concerns, not owned by a single business module.

### Business / module models → `app/Modules/{Module}/Models/`

Models that belong to one business domain stay colocated with their module:

- Content: Content
- Banners: Banner
- Menus: Menu, MenuItem

New business modules must place their primary models in the module namespace.

### Services

- Framework services: `app/Services/` or future `app/Framework/` namespace
- Module-specific services: `app/Modules/{Module}/Services/`

### Migrations

- Module-owned tables: migrations under the owning module (flat `Migrations/`
  or nested `Database/Migrations/` per [MODULE_STANDARD.md](../MODULE_STANDARD.md))
- Shared/kernel tables: migration in the module that introduced them, or
  a dedicated framework module when one exists

No mass migration of existing models is required in Phase 1. Relocation
happens only when a module is otherwise being refactored.

## Consequences

### Positive

- Clear rule for new development
- Framework models stay discoverable in one place
- Business modules remain self-contained for their domain
- Backward compatible with current MVP layout

### Negative

- Two valid locations until legacy modules are optionally refactored
- Import paths differ (`App\Models\User` vs `App\Modules\Content\Models\Content`)

## Compliance

- New business modules: models in `app/Modules/{Module}/Models/`
- New framework/kernel models: `app/Models/` (or documented framework path)
- Do not move existing models without an explicit migration task
