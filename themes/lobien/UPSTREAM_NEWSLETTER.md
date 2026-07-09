# Lobien — Upstream Newsletter Setup (Market Outlook Section)

Apply these changes in the **FYD CMS upstream repository** (core + `contrib/Newsletter`), not in the Lobien downstream site repo (`fyd-lobien`).

The Lobien theme (downstream) renders the Page Manager **Newsletter** block using slug `market-outlook` and expects this list to exist with specific name, description, and field settings.

## 1. Demo newsletter list seeder

**File:** `contrib/Newsletter/Database/Seeders/DemoNewsletterSeeder.php`  
(After module install, mirrored at `app/Modules/Newsletter/Database/Seeders/DemoNewsletterSeeder.php`)

Add a second `updateOrCreate` after `site-updates`:

```php
NewsletterList::updateOrCreate(
    ['slug' => 'market-outlook'],
    [
        'name' => 'Real Estate Market Outlook Reports',
        'description' => 'Be informed about the current performance and trends of the Philippine commercial real estate. Fill out your details and download our latest market outlook now!',
        'is_active' => true,
        'settings' => array_merge(NewsletterList::defaultSettings(), [
            'get_name' => true,
            'get_company' => true,
            'subscribe_label' => 'Download',
        ]),
    ],
);
```

Keep the existing `site-updates` entry unchanged (tests depend on it).

## 2. Run seeder

```bash
php artisan db:seed --class=App\\Modules\\Newsletter\\Database\\Seeders\\DemoNewsletterSeeder
```

Or full install/migrate flow if that re-runs module seeders.

## 3. Page Manager — home block (manual or seeder)

On the Lobien home page (`/`), **Main** region needs a **Newsletter** block:

| Block setting | Value |
|---------------|-------|
| Block type | `newsletter` |
| Newsletter List (`list_slug`) | `market-outlook` |

Optional upstream seeder addition in `PageManagerSeeder` (home page blocks), e.g.:

```php
['region_key' => 'main', 'block_type' => 'newsletter', 'sort_order' => N, 'config' => ['list_slug' => 'market-outlook']],
```

Adjust `sort_order` so it appears below the What's New / `latest-articles` content block.

## 4. Verify API

```bash
curl -s http://localhost/api/newsletters/market-outlook | jq .
```

Expect JSON with:

- `name`: Real Estate Market Outlook Reports
- `description`: (intro paragraph)
- `settings.fields.name.enabled`: true
- `settings.fields.company.enabled`: true
- `settings.subscribe_label`: Download

## 5. Downstream (Lobien theme only)

No Newsletter PHP changes in `fyd-lobien`. After upstream seed + Page Manager config:

1. Sync Lobien theme: `contrib_themes/lobien` → `themes/lobien`
2. `npm run build`
3. Hard refresh `/`

Theme files involved (already in downstream):

- `contrib_themes/lobien/js/blocks/NewsletterBlock.vue`
- `contrib_themes/lobien/js/Components/LobienNewsletterOutlookSection.vue`
- `contrib_themes/lobien/js/composables/useNewsletter.js`
- `contrib_themes/lobien/assets/images/market-outlook.jpg`

## 6. Alternative: admin-only (no seeder)

Create list manually in **Admin → Newsletter Lists**:

- Slug: `market-outlook`
- Name / Description: as above
- Enable **Name** and **Company** in list settings
- Subscribe button label: `Download`
