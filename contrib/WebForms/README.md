# WebForms Module

Installable business module for dynamic web forms with JSON schema storage.

## Install

1. Copy `contrib/WebForms` to `app/Modules/WebForms`
2. Administration → Modules → Rescan → Install **WebForms**

## Features

- Admin CRUD for webforms
- Visual form builder (fields, validation, dropdown options, dates)
- Submissions list with search, filter, bulk delete, and detail view
- Public JSON API at `/api/webforms/{slug}` and `/api/webforms/{slug}/submit`
- reCAPTCHA v3 on all public submissions
- Attach forms to one or more content pages (auto-embed on `Content/Show`; one form per page site-wide)
- Reusable `WebformRenderer` Vue component for custom placement
- Standalone public page at `/forms/{slug}`

## Permissions

- `webforms.view`, `webforms.create`, `webforms.edit`, `webforms.delete`
- `webforms.submissions.view`, `webforms.submissions.delete`
