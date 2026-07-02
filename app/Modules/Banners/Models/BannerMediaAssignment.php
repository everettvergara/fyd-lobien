<?php

namespace App\Modules\Banners\Models;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannerMediaAssignment extends Model
{
    protected $fillable = [
        'banner_id',
        'banner_slide_id',
        'banner_content_block_id',
        'slot',
        'media_id',
        'sort_order',
        'alt_text',
        'title_attribute',
        'aria_label',
        'caption',
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

    public function contentBlock(): BelongsTo
    {
        return $this->belongsTo(BannerContentBlock::class, 'banner_content_block_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
