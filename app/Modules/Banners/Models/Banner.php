<?php

namespace App\Modules\Banners\Models;

use App\Enums\BannerType;
use App\Enums\ContentStatus;
use App\Models\Media;
use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasSeo, SoftDeletes;

    protected $fillable = [
        'name', 'key', 'title', 'subtitle', 'description', 'internal_notes', 'type', 'template_id',
        'desktop_image_id', 'mobile_image_id', 'background_image_id',
        'button_text', 'button_url', 'sort_order', 'status', 'settings', 'effect_settings',
    ];

    protected function casts(): array
    {
        return [
            'type' => BannerType::class,
            'status' => ContentStatus::class,
            'settings' => 'array',
            'effect_settings' => 'array',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published);
    }

    public function isActive(): bool
    {
        return $this->status === ContentStatus::Published;
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(BannerTemplate::class, 'template_id');
    }

    public function slides(): HasMany
    {
        return $this->hasMany(BannerSlide::class)->orderBy('sort_order');
    }

    public function contentBlocks(): HasMany
    {
        return $this->hasMany(BannerContentBlock::class)->orderBy('sort_order');
    }

    public function mediaAssignments(): HasMany
    {
        return $this->hasMany(BannerMediaAssignment::class)->orderBy('sort_order');
    }

    public function desktopImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'desktop_image_id');
    }

    public function mobileImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'mobile_image_id');
    }

    public function backgroundImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'background_image_id');
    }
}
