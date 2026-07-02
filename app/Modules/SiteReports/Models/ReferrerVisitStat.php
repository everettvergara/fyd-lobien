<?php

namespace App\Modules\SiteReports\Models;

use Illuminate\Database\Eloquent\Model;

class ReferrerVisitStat extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $primaryKey = 'referer_host';

    protected $casts = [
        'hit_count' => 'integer',
        'last_visited_at' => 'datetime',
    ];
}
