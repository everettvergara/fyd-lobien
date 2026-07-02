<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaUsage extends Model
{
    protected $table = 'media_usage';

    protected $fillable = [
        'media_id',
        'usable_type',
        'usable_id',
        'module',
        'field',
        'label',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function usable(): MorphTo
    {
        return $this->morphTo();
    }
}
