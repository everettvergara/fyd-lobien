<?php

namespace App\Modules\Banners\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BannerSlide extends Model
{
    protected $fillable = [
        'banner_id',
        'name',
        'sort_order',
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

    public function contentBlocks(): HasMany
    {
        return $this->hasMany(BannerContentBlock::class)->orderBy('sort_order');
    }

    public function mediaAssignments(): HasMany
    {
        return $this->hasMany(BannerMediaAssignment::class)->orderBy('sort_order');
    }
}
