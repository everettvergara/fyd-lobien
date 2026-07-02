# FYD CMS Framework

## Framework Modules

The following modules form the reusable kernel of the CMS.

-   Security
-   Settings
-   Module Registry
-   Menu Registry
-   Theme Manager *(planned / deferred — manual themes documented in [THEME.md](THEME.md))*
-   Media Library
-   Localization
-   Notifications
-   Audit Logs
-   Standard Admin List

## Installable business modules

Business modules are authored in [`contrib/`](../contrib/) and installed via
**Administration → Modules** (Module Manager). They:

-   Register through `Module.php` with `isInstallable(): true`
-   Appear as a **group** in the admin **right sidebar panel** (e.g. E-Commerce)
-   Expose **feature modules** as menu items under that group (Products, Orders, …)
-   Use install / disable / uninstall lifecycle with mandatory confirmations

Core framework modules stay in the **left sidebar panel**. See
[MODULE_LIFECYCLE.md](MODULE_LIFECYCLE.md) and [MODULE_CONTRIBUTION.md](MODULE_CONTRIBUTION.md).

## Rules

-   Do not duplicate framework functionality.
-   Every business module shall register itself with the framework.
-   Every upload shall use the Media Library.
-   Every configurable option shall use the Settings framework.
-   Every administrative action shall be audited.
-   Every applicable admin index/list shall use the Standard Admin List
    framework for search, sortable columns, pagination, inline icon row
    actions, and bulk actions where applicable.
-   Filters are optional and defined per module only when explicitly
    requested. Search fields must be explicitly configured per module.
-   Standard admin lists shall render row actions as inline icon buttons
    at the side of each row. Do not use dropdown action menus unless a
    documented technical exception exists.

## Media Library Framework

Media Library is a framework service. The Media module is the admin UI
consumer of that framework.

Rules:

-   Business modules must use `App\Services\Media\MediaLibraryService` or
    the focused services under `App\Services\Media`.
-   Business modules must register asset references through
    `MediaUsageService`.
-   Controllers must remain thin and delegate media behavior to services.
-   Storage provider details must stay behind `MediaStorageService`.
-   Deletion must be usage-aware and soft delete by default.

Detailed contract: [MEDIA_LIBRARY.md](MEDIA_LIBRARY.md).

## Banner Presentation Framework

The Banner module is the standard reusable visual presentation system for
marketing and layout banners. It must be consumed through its services and
public renderer instead of rebuilding homepage-specific slider logic.

Rules:

-   Placements are database records, not hardcoded enum-only values.
-   Templates are reusable and configurable.
-   Banner media must use the Media Library and usage tracker.
-   Public rendering should use the Banner rendering service or Vue
    `BannerRenderer` component.

Detailed contract: [BANNER_MODULE.md](BANNER_MODULE.md).
