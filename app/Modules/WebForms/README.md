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
- Automatic public page at `/{slug}` for each active webform (with `webform` block)
- Reusable `WebformRenderer` Vue component for custom placement via Page Manager blocks

## Permissions

- `webforms.view`, `webforms.create`, `webforms.edit`, `webforms.delete`
- `webforms.submissions.view`, `webforms.submissions.delete`
