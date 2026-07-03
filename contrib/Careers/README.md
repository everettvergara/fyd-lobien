# Careers Module

Installable business module for job listings, public applications, and admin submission management.

## Features

- **Job Listings** — CRUD with picture (Media Library), department, location, employment type, salary, summary, description, requirements, publishing controls
- **Page Configuration** — attach the public job listing grid to multiple content page slugs
- **Applications** — inbox with per-job filter, resume download (private storage), bulk delete

## Public routes

| Route | Purpose |
|-------|---------|
| `GET /api/careers/jobs` | JSON list of open jobs |
| `GET /careers/{slug}` | Job detail + application form |
| `POST /api/careers/jobs/{slug}/apply` | Submit application (PDF + reCAPTCHA v3) |

## Install

Copy to `app/Modules/Careers/` (or use `php artisan module:install Careers`), then assign permissions to roles.

## Development

Author changes in `contrib/Careers/`. Requires core modules: AuditLogs, Content, Media.
