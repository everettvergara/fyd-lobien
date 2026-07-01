# FYD CMS Module Standard

Every module shall follow the same structure.

    Module/
     ├── Controllers
     ├── Models
     ├── Requests
     ├── Policies
     ├── Services
     ├── Routes
     ├── Views
     ├── Database
     │    ├── Migrations
     │    └── Seeders
     ├── Tests
     └── Module.php

Requirements

-   CRUD where applicable
-   Form Requests for validation
-   Laravel Policies
-   Feature Tests
-   Registration through Module Registry
-   Registration through Menu Registry
-   Activity logging

## MVP Compatibility

Existing modules may use flat `Migrations/` and `Seeders/` folders at the
module root. New modules should use the `Database/` nesting shown above.
During Framework Stabilization, existing modules will migrate incrementally
without breaking changes.

See [ADR 001](ADR/001-model-placement.md) for model location rules.
