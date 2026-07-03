<?php

namespace App\Modules\PageManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageMaster extends Model
{
    protected $fillable = [
        'default_seo_title_suffix',
        'default_robots',
        'default_sitemap_changefreq',
        'default_sitemap_priority',
        'is_configured',
    ];

    protected function casts(): array
    {
        return [
            'is_configured' => 'boolean',
            'default_sitemap_priority' => 'float',
        ];
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(PageMasterBlock::class)->orderBy('sort_order');
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate([], [
            'default_robots' => 'index,follow',
            'default_sitemap_changefreq' => 'monthly',
            'default_sitemap_priority' => 0.5,
            'is_configured' => false,
        ]);
    }
}
