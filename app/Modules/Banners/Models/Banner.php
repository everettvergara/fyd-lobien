<?php

namespace App\Modules\Banners\Models;

use App\Enums\BannerPlacement;
use App\Enums\BannerType;
use App\Enums\ContentStatus;
use App\Models\Media;
use App\Traits\HasExpiry;
use App\Traits\HasSeo;
use App\Traits\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasExpiry, HasSeo, Publishable, SoftDeletes;

    protected $fillable = [
        'name', 'title', 'subtitle', 'description', 'type', 'placement',
        'desktop_image_id', 'mobile_image_id', 'background_image_id',
        'button_text', 'button_url', 'sort_order', 'status',
        'published_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => BannerType::class,
            'placement' => BannerPlacement::class,
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
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
