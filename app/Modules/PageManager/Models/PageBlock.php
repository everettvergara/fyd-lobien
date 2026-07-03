<?php

namespace App\Modules\PageManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageBlock extends Model
{
    protected $fillable = [
        'page_id',
        'region_key',
        'block_type',
        'sort_order',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
