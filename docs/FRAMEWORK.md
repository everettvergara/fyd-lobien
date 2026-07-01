# FYD CMS Framework

## Framework Modules

The following modules form the reusable kernel of the CMS.

-   Security
-   Settings
-   Module Registry
-   Menu Registry
-   Theme Manager
-   Media Library
-   Localization
-   Notifications
-   Audit Logs

## Rules

-   Do not duplicate framework functionality.
-   Every business module shall register itself with the framework.
-   Every upload shall use the Media Library.
-   Every configurable option shall use the Settings framework.
-   Every administrative action shall be audited.
