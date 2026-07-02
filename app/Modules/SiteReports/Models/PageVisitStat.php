<?php

namespace App\Modules\SiteReports\Models;

use Illuminate\Database\Eloquent\Model;

class PageVisitStat extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $primaryKey = 'path';

    protected $casts = [
        'hit_count' => 'integer',
        'last_visited_at' => 'datetime',
    ];
}
