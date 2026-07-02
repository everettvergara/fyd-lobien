# FYD CMS Architecture

## Purpose

This document defines the long-term architecture of the FYD CMS. It is
the authoritative architectural reference.

## Architectural Principles

-   Framework first, business modules second.
-   Prefer reusable services over duplicated logic.
-   Thin controllers, rich services.
-   Modules are self-contained.
-   Configuration belongs in Settings, not code.

## High-Level Layers

1.  Presentation (Blade / Vue)
2.  Controllers
3.  Form Requests
4.  Services
5.  Models
6.  Database

## Framework Services

-   Security
-   Settings
-   Module Registry
-   Menu Registry
-   Media Library
-   Theme Manager *(planned / deferred — manual themes documented in [THEME.md](THEME.md))*
-   Notifications
-   Audit Logs
-   Localization

Business modules must consume these services rather than implementing
their own.

### Media Library

The Media Library is the central Digital Asset Management framework for
all uploads and asset references. Modules must not implement their own
upload, picker, storage, thumbnail, download, folder, or usage tracking
logic when the Media Library provides the capability.

See [MEDIA_LIBRARY.md](MEDIA_LIBRARY.md) for the media framework service,
database, storage, permission, and UI contracts.

## Dependency Rules

-   Modules must not directly depend on other modules where a framework
    service exists.
-   Controllers never contain business logic.
-   Views never access the database directly.

## Long-term Goal

FYD CMS is intended to become a reusable application framework for
future client projects.
