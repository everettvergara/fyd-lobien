<?php

namespace App\Modules\PropertyListings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingBuildingService extends Model
{
    protected $fillable = [
        'listing_id',
        'operating_hours',
        'ac_system',
        'no_of_lifts_passenger',
        'no_of_lifts_service',
        'telco',
        'backup_power',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
