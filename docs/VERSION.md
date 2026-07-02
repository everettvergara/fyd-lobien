# CMS Template Version

This document tracks the **FYD Laravel Bootstrap CMS** template version. Update it whenever you modify the template.

## Current Version

| Field | Value |
|-------|-------|
| **Version** | 1.0.0 |
| **Major** | 1 |
| **Minor** | 0 |
| **Release** | 0 |

The version is shown at the bottom of the admin panel sidebar.

## Source of Truth

Version numbers live in [`config/cms.php`](../config/cms.php):

```php
'version' => [
    'major' => 1,
    'minor' => 0,
    'release' => 0,
],
```

Runtime access:

```php
use App\Support\CmsVersion;

CmsVersion::string();   // "0.0.0"
CmsVersion::major();    // 0
CmsVersion::minor();    // 0
CmsVersion::release();  // 0
CmsVersion::info();     // full array for views
```

## When to Bump

| Segment | Bump when… | Example |
|---------|------------|---------|
| **Major** | Breaking changes: removed modules, incompatible migrations, changed admin/public contracts | `1.0.0` → `2.0.0` |
| **Minor** | New features or modules that stay backward compatible | `0.1.0` → `0.2.0` |
| **Release** | Bug fixes, UI tweaks, docs, refactors with no new features | `0.0.0` → `0.0.1` |

Reset lower segments when bumping a higher one (e.g. `0.2.5` → `1.0.0`).

## Update Checklist

On every template modification:

1. Bump the appropriate segment in `config/cms.php`
2. Update the **Current Version** table in this file
3. Add a changelog entry below with date and summary

## Changelog

### 0.0.0 — 2026-07-02

- Initial version tracking for the CMS template
- Admin sidebar footer displays version, major, minor, and release
