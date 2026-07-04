<?php

namespace App\Modules\PropertyListings\Models;

use Illuminate\Database\Eloquent\Model;

class ListingLookup extends Model
{
    protected $fillable = [
        'group',
        'value',
        'label',
        'sort_order',
        'is_active',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'meta' => 'array',
        ];
    }
}
