<?php

namespace App\Modules\PropertyListings\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingRemark extends Model
{
    protected $fillable = [
        'listing_id',
        'listing_unit_id',
        'user_id',
        'comment',
        'remarked_at',
    ];

    protected function casts(): array
    {
        return [
            'remarked_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ListingUnit::class, 'listing_unit_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
