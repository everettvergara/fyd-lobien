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
-   Theme Manager
-   Notifications
-   Audit Logs
-   Localization

Business modules must consume these services rather than implementing
their own.

## Dependency Rules

-   Modules must not directly depend on other modules where a framework
    service exists.
-   Controllers never contain business logic.
-   Views never access the database directly.

## Long-term Goal

FYD CMS is intended to become a reusable application framework for
future client projects.
