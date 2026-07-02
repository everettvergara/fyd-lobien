<?php

namespace App\Modules\Banners\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BannerTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'schema',
        'default_settings',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'schema' => 'array',
            'default_settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function banners(): HasMany
    {
        return $this->hasMany(Banner::class, 'template_id');
    }
}
