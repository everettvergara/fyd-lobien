<?php

namespace App\Modules\PropertyListings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingOtherInfo extends Model
{
    protected $fillable = [
        'listing_id',
        'peza_accreditation',
        'sustainability',
        'other_info_visible',
    ];

    protected function casts(): array
    {
        return [
            'other_info_visible' => 'boolean',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
