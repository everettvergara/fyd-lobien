# FYD CMS Documentation

This directory is the authoritative source for FYD CMS architecture and
development standards.

## Reading Order

1. [ARCHITECTURE.md](ARCHITECTURE.md) — Long-term architecture and principles
2. [FRAMEWORK.md](FRAMEWORK.md) — Framework kernel modules and rules
3. [MODULE_STANDARD.md](MODULE_STANDARD.md) — Required module structure
4. [MODULE_CONTRIBUTION.md](MODULE_CONTRIBUTION.md) — Authoring installable business modules in `contrib/`
5. [MODULE_LIFECYCLE.md](MODULE_LIFECYCLE.md) — Install, disable, uninstall via admin Modules page
6. [SECURITY.md](SECURITY.md) — Security domain specification
7. [MEDIA_LIBRARY.md](MEDIA_LIBRARY.md) — Digital Asset Management framework
8. [CONTENT_MODULE.md](CONTENT_MODULE.md) — Unified content module and content types
9. [PAGE_MANAGER.md](PAGE_MANAGER.md) — Public site pages, block composition, and SEO
10. [BANNER_MODULE.md](BANNER_MODULE.md) — Reusable banner presentation engine
11. [SEEDING.md](SEEDING.md) — Database seeders, sample content, and new-install data
12. [DEVELOPMENT.md](DEVELOPMENT.md) — Day-to-day development guide
13. [THEME.md](THEME.md) — Public theme system, tokens, and styling (**read before color/font/theme tasks**)
14. [ROADMAP.md](ROADMAP.md) — Phases, milestones, and current work
15. [VERSION.md](VERSION.md) — CMS template version and changelog

## Document Hierarchy

| Document | Scope | Audience |
|----------|-------|----------|
| ARCHITECTURE.md | Layers, framework services, dependency rules | Architects, lead developers |
| FRAMEWORK.md | Kernel modules, framework rules | Framework contributors |
| MODULE_STANDARD.md | Module folder layout and requirements | Module authors |
| MODULE_CONTRIBUTION.md | Business modules in `contrib/`, manifest, features | Business module authors |
| MODULE_LIFECYCLE.md | Copy, install, disable, uninstall, Modules admin page | Site owners, integrators |
| SECURITY.md | Auth, RBAC, audit, sessions | Security-sensitive work |
| MEDIA_LIBRARY.md | Media services, storage, usage, UI integration | Framework and module authors |
| CONTENT_MODULE.md | Content module, content-types registry, admin/public integration | Content module authors |
| BANNER_MODULE.md | Banner templates, keys, rendering, extension points | Content and marketing module authors |
| SEEDING.md | Seeders, essential vs sample data, new-install inventory | All developers, project setup |
| DEVELOPMENT.md | Portals, modules, conventions, testing | All developers |
| THEME.md | Public themes, design tokens, install/activate workflow, agent rules | All developers, **Cursor agents** |
| ROADMAP.md | Phase status and priorities | Planning, prioritization |
| VERSION.md | CMS template semver, bump rules, changelog | All developers |

## Business modules (`contrib/`)

Installable business modules are **authored** under [`../contrib/`](../contrib/)
and **installed** by copying to `app/Modules/` and using **Administration →
Modules**. See [MODULE_CONTRIBUTION.md](MODULE_CONTRIBUTION.md) and
[MODULE_LIFECYCLE.md](MODULE_LIFECYCLE.md).

## Architectural Decision Records

| ADR | Title |
|-----|-------|
| [001](ADR/001-model-placement.md) | Model placement (framework vs module) |

## Current Phase

**Framework Stabilization** — See [ROADMAP.md](ROADMAP.md).

Core modules must remain stable before new installable business modules ship in
`contrib/`. Module lifecycle documentation is in place ahead of the Module
Manager implementation.

## Related

- Project overview and quick start: [../README.md](../README.md)
- Business module source tree: [../contrib/README.md](../contrib/README.md)
