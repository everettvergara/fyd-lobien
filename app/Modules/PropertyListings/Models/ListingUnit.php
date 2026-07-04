<?php

namespace App\Modules\PropertyListings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingUnit extends Model
{
    protected $fillable = [
        'listing_id',
        'floor',
        'unit',
        'area_size',
        'rental',
        'handover_condition',
        'availability',
        'bedrooms',
        'selling_price',
        'property_type',
        'for_lease',
        'for_sale',
        'last_remarks',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'area_size' => 'decimal:2',
            'rental' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'for_lease' => 'boolean',
            'for_sale' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
