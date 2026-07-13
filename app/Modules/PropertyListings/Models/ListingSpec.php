<?php

namespace App\Modules\PropertyListings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingSpec extends Model
{
    protected $fillable = [
        'listing_id',
        'developer',
        'grade',
        'completion_year',
        'completion_qtr',
        'no_of_floors',
        'no_of_basement',
        'density_ratio',
        'parking_allocation',
        'floor_to_ceiling_height',
        'gross_leasable_area',
        'typical_floor_area',
        'typical_retail_floor_area',
        'floor_efficiency',
    ];

    protected function casts(): array
    {
        return [
            'completion_year' => 'integer',
            'gross_leasable_area' => 'decimal:2',
            'typical_floor_area' => 'decimal:2',
            'typical_retail_floor_area' => 'decimal:2',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
