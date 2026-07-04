# FYD CMS Roadmap

## Completed

-   MVP Foundation
-   Authentication
-   Theme System
-   Page Management
-   SEO Foundation
-   Banner Management foundation

## Current Phase

Framework Stabilization

Focus on:

-   Security
-   Framework Modules
-   Standardization
-   Documentation
-   UI Consistency

### Framework Stabilization Phases

1.  **Foundation & Documentation** — Consolidate docs, remove legacy artifacts, ADRs *(complete)*
2.  **Core Framework Services** — Module Registry, Menu Registry, Settings Service, login history, system role protection *(complete)*
3.  **Security & Audit** — Password policies, Authentication Settings, Audit Logs module, session management *(complete)*
4.  **Media & Content Framework** — Media Service, Publishing Service, media picker, content search, SEO formalization *(complete)*
5.  **Module Standardization & UI** — Standard Admin List framework, shared admin Blade components, `Database/` nesting pilot, service extraction *(current)*
6.  **Business Modules & Module Manager** — Installable modules in `contrib/`, Modules admin page, lifecycle (install / disable / uninstall), dual-panel sidebar *(in progress — see [MODULE_LIFECYCLE.md](MODULE_LIFECYCLE.md))*

## Next Milestones

1.  Framework Modules
2.  Content Engine — Content Blocks module (Views-style aggregation)
3.  Marketing modules built on Banner keys/templates
4.  Commerce
5.  Client-specific Modules

## Development Rule

Do not build new installable business modules in `contrib/` until framework
services and the Module Manager lifecycle are implemented. Documentation:
[MODULE_CONTRIBUTION.md](MODULE_CONTRIBUTION.md), [MODULE_LIFECYCLE.md](MODULE_LIFECYCLE.md).
