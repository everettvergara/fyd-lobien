# Careers Module

Installable business module for job listings, public applications, and admin submission management.

## Features

- **Job Listings** — CRUD with picture (Media Library), department, location, employment type, salary, summary, description, requirements, publishing controls
- **Public Careers Page** — on install, creates `/careers` with a `careers-listing` block (only if the page does not already exist)
- **Applications** — inbox with per-job filter, resume download (private storage), bulk delete

## Public routes

| Route | Purpose |
|-------|---------|
| `GET /careers` | Page Manager listing of open jobs (paginated via `?page=`) |
| `GET /api/careers/jobs` | JSON list of open jobs with pagination (`?page=`, `?per_page=`) |
| `GET /careers/{slug}` | Job detail + application form |
| `POST /api/careers/jobs/{slug}/apply` | Submit application (PDF + reCAPTCHA v3) |

## Public job fields

The `careers-listing` block resolver and API expose all public job fields for theme formatting:

`id`, `title`, `slug`, `summary`, `description`, `requirements`, `department`, `location`, `salary_range`, `employment_type`, `employment_type_label`, `closing_date`, `published_at`, `sort_order`, `picture`, `url`

Each listing links to `/careers/{slug}` via the `url` field.

## Pagination

- Default: 9 jobs per page
- Page query: `/careers?page=2`
- Admin can configure `per_page` on the `careers-listing` block in Page Manager (1–48)
- Themes style the listing via shared block CSS classes (`.careers-listing`, `.careers-job`, etc.)

## Install

Copy to `app/Modules/Careers/` (or use `php artisan module:install Careers`), then assign permissions to roles.

On install, demo jobs are seeded and the `/careers` page is created automatically when missing.

## Development

Author changes in `contrib/Careers/`. Requires core modules: AuditLogs, Content, Media.
