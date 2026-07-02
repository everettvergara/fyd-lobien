<?php

namespace App\Modules\SiteReports\Models;

use Illuminate\Database\Eloquent\Model;

class IpVisitStat extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $primaryKey = 'ip_address';

    protected $casts = [
        'hit_count' => 'integer',
        'blocked_ip_id' => 'integer',
        'last_visited_at' => 'datetime',
    ];
}
