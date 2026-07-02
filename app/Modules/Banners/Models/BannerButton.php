<?php

namespace App\Modules\Banners\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannerButton extends Model
{
    protected $fillable = [
        'banner_content_block_id',
        'label',
        'url',
        'target',
        'style',
        'icon',
        'sort_order',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function contentBlock(): BelongsTo
    {
        return $this->belongsTo(BannerContentBlock::class, 'banner_content_block_id');
    }
}
