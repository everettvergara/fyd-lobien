# FYD CMS Documentation

This directory is the authoritative source for FYD CMS architecture and
development standards.

## Reading Order

1. [ARCHITECTURE.md](ARCHITECTURE.md) — Long-term architecture and principles
2. [FRAMEWORK.md](FRAMEWORK.md) — Framework kernel modules and rules
3. [MODULE_STANDARD.md](MODULE_STANDARD.md) — Required module structure
4. [SECURITY.md](SECURITY.md) — Security domain specification
5. [DEVELOPMENT.md](DEVELOPMENT.md) — Day-to-day development guide
6. [ROADMAP.md](ROADMAP.md) — Phases, milestones, and current work

## Document Hierarchy

| Document | Scope | Audience |
|----------|-------|----------|
| ARCHITECTURE.md | Layers, framework services, dependency rules | Architects, lead developers |
| FRAMEWORK.md | Kernel modules, framework rules | Framework contributors |
| MODULE_STANDARD.md | Module folder layout and requirements | Module authors |
| SECURITY.md | Auth, RBAC, audit, sessions | Security-sensitive work |
| DEVELOPMENT.md | Portals, modules, conventions, testing | All developers |
| ROADMAP.md | Phase status and priorities | Planning, prioritization |

## Architectural Decision Records

| ADR | Title |
|-----|-------|
| [001](ADR/001-model-placement.md) | Model placement (framework vs module) |

## Current Phase

**Framework Stabilization** — See [ROADMAP.md](ROADMAP.md).

Do not add new business modules until framework services are stable.

## Related

- Project overview and quick start: [../README.md](../README.md)
