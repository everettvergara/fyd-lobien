<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media extends Model
{
    use SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'uuid',
        'folder_id',
        'filename',
        'original_filename',
        'title',
        'description',
        'caption',
        'alt_text',
        'copyright',
        'credit',
        'mime_type',
        'extension',
        'size',
        'width',
        'height',
        'duration',
        'disk',
        'storage_provider',
        'path',
        'visibility',
        'checksum',
        'uploaded_by',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Media $media) {
            $media->uuid ??= (string) Str::uuid();
        });
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(MediaTag::class, 'media_media_tag');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(MediaHistory::class);
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function displayName(): string
    {
        return $this->title ?: $this->original_filename;
    }

    public function variantUrl(string $variant): ?string
    {
        $record = $this->relationLoaded('variants')
            ? $this->variants->firstWhere('variant', $variant)
            : $this->variants()->where('variant', $variant)->first();

        if (! $record || ! Storage::disk($record->disk)->exists($record->path)) {
            return null;
        }

        return $record->url();
    }
}
