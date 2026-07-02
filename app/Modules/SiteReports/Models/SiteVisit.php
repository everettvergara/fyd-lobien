<?php

namespace App\Modules\SiteReports\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'path',
        'route_name',
        'ip_address',
        'user_agent',
        'referer',
        'referer_host',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }
}
