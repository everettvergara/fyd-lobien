# FYD CMS Media Library Framework

## Purpose

The Media Library is the Digital Asset Management framework for FYD CMS.
It is a shared framework service, not a business-module upload feature.

All current and future modules must use the Media Library for uploads,
asset selection, previews, downloads, metadata, variants, folders, and
usage tracking.

## Framework Services

Reusable media services live under `App\Services\Media`.

| Service | Responsibility |
|---------|----------------|
| `MediaLibraryService` | Framework facade for consumers |
| `MediaUploadService` | Single/multiple upload and replacement orchestration |
| `MediaStorageService` | Storage abstraction over Laravel disks |
| `MediaMetadataService` | Metadata and tag updates |
| `MediaSearchService` | Server-side search, filter, sort, pagination, picker payloads |
| `MediaFolderService` | Nested folder create, rename, move, delete |
| `MediaVariantService` | Original/thumbnail/size/conversion variant records |
| `MediaUsageService` | Registers where assets are used |
| `MediaDeletionService` | Archive, restore, soft delete, permanent delete |
| `MediaDownloadService` | Single, variant, and ZIP downloads |
| `MediaBulkActionService` | Reusable bulk action dispatcher |
| `MediaPreviewService` | Preview payloads for UI components |
| `MediaPreferenceService` | User media preferences such as view mode |

Controllers must call these services rather than placing media business
logic in controllers or business modules.

## Kernel Models

Media framework models are shared kernel models in `app/Models/`:

- `Media`
- `MediaFolder`
- `MediaVariant`
- `MediaTag`
- `MediaUsage`
- `MediaHistory`
- `MediaUserPreference`

## Database Structures

The framework uses:

- `media` for the canonical asset record and metadata.
- `media_folders` for nested folders.
- `media_variants` for generated/original variants.
- `media_tags` and `media_media_tag` for tags.
- `media_usage` for polymorphic asset usage tracking.
- `media_history` for asset history.
- `media_user_preferences` for user media preferences.

Deletion must consult `media_usage` before removing assets. Soft delete is
the default. Permanent delete is an explicit operation.

## Storage

Initial storage uses Laravel's `public` disk. Business logic must not call
`Storage` directly for media operations; use `MediaStorageService` or a
higher-level media service.

After setup or deployment, create the public storage symlink:

```bash
php artisan storage:link
```

Without this link, uploaded files are stored correctly in
`storage/app/public`, but browser URLs such as `/storage/media/...` will
404 and images will appear broken in the admin UI and public site.

The stored asset records include `disk`, `storage_provider`, `path`, and
`visibility` so future providers such as S3, Azure Blob, and Cloudflare R2
can be added without changing business modules.

## Usage Tracking

Modules that reference media must register usage through
`MediaUsageService`.

Example:

```php
app(MediaUsageService::class)->syncModel($content, 'content', [
    'featured_image_id' => 'Featured Image',
]);
```

Usage should be synced after create/update and removed when the owning
model is deleted.

## Permissions

Media permissions follow the standard `{module}.{action}` format:

- `media.view`
- `media.create`
- `media.edit`
- `media.delete`
- `media.download`
- `media.replace`
- `media.bulk_delete`
- `media.bulk_download`
- `media.folders`

All media controllers and components must be permission-aware.

## UI Components

The Media module exposes reusable Blade components/partials for:

- Media picker
- Upload queue/dialog
- Preview dialog
- Metadata editor
- Folder picker
- Bulk action toolbar
- Thumbnail/media card
- File list

Future modules must consume these components instead of implementing their
own upload or picker UI.

## Upload Limits

Application-level limits are configured through Settings group `media`.
PHP/server limits such as `post_max_size` still apply. Oversized requests
are handled as controlled validation-style errors instead of exception
pages.
