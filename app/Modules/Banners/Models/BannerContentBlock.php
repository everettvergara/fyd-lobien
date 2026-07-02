<?php

namespace App\Modules\Banners\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BannerContentBlock extends Model
{
    protected $fillable = [
        'banner_id',
        'banner_slide_id',
        'region',
        'type',
        'sort_order',
        'headline',
        'subheading',
        'description',
        'rich_text',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }

    public function slide(): BelongsTo
    {
        return $this->belongsTo(BannerSlide::class, 'banner_slide_id');
    }

    public function buttons(): HasMany
    {
        return $this->hasMany(BannerButton::class)->orderBy('sort_order');
    }
}
