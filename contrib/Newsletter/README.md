# Newsletter Module

Installable business module for newsletter subscription lists, subscriber management, and batch email campaigns.

## Install

1. Copy `contrib/Newsletter` to `app/Modules/Newsletter`
2. Administration → Modules → Rescan → Install **Newsletter**
3. Assign permissions to roles

## Features

- Admin CRUD for newsletter lists (each with a unique slug)
- Subscriber list with search, filters, bulk delete/unsubscribe, and CSV export
- Public JSON API at `/api/newsletters/{slug}`, `/subscribe`, and `/unsubscribe`
- reCAPTCHA v3 on all public subscribe/unsubscribe POSTs
- Token-based one-click email opt-out at `/newsletters/unsubscribe/{token}`
- Batch send with rich-text editor; queued emails include mandatory unsubscribe link
- Send history audit log

## Theme integration

Newsletter lists can be placed in three ways:

### 1. Admin attachments (recommended)

When editing a newsletter list in admin, configure:

- **Attach to pages** — auto-embeds on matching content pages (`Content/Show`)
- **Attach to theme sections** — auto-embeds in layout regions (Footer, Sidebar, Header bar)

Section attachments are shared on every public page via the Inertia `newsletters` prop, e.g. `newsletters.footer.slug`.

### 2. Manual component

Embed a subscription form anywhere in your theme:

```vue
<script setup>
import NewsletterRenderer from '../Components/NewsletterRenderer.vue';
</script>

<template>
    <NewsletterRenderer slug="site-updates" />
</template>
```

The slug must match an active newsletter list created in admin. A demo list with slug `site-updates` is seeded on install.

## Permissions

- `newsletter-lists.view|create|edit|delete`
- `newsletter-subscribers.view|create|edit|delete|export`
- `newsletters.send`
- `newsletter-sends.view`

## Queue

Batch sends use queued notifications. Run `php artisan queue:work` and configure SMTP under Settings → Email.
