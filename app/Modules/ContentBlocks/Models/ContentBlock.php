<?php

namespace App\Modules\ContentBlocks\Models;

use App\Enums\ContentStatus;
use App\Modules\ContentBlocks\Enums\ContentBlockFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{
    protected $fillable = [
        'key',
        'name',
        'icon',
        'status',
        'content_types',
        'fields',
        'filters',
        'sort_field',
        'sort_direction',
        'items_per_page',
        'pagination_enabled',
        'formatter',
        'wrapper_class',
        'wrapper_id',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'content_types' => 'array',
            'fields' => 'array',
            'filters' => 'array',
            'pagination_enabled' => 'boolean',
            'formatter' => ContentBlockFormatter::class,
            'settings' => 'array',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published);
    }
}
