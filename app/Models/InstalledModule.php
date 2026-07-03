<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstalledModule extends Model
{
    public const STATUS_INSTALLED = 'installed';

    public const STATUS_DISABLED = 'disabled';

    protected $fillable = [
        'name',
        'status',
        'version',
        'installed_at',
        'disabled_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'installed_at' => 'datetime',
            'disabled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_INSTALLED;
    }
}
