<?php

namespace App\Modules\PageManager\Models;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\User;
use App\Traits\HasSeo;
use App\Traits\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasSeo, Publishable, SoftDeletes;

    protected $fillable = [
        'path',
        'slug',
        'title',
        'summary',
        'body',
        'featured_image_id',
        'status',
        'published_at',
        'author_id',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'is_system' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('sort_order');
    }

    public static function normalizePath(string $path): string
    {
        $path = trim($path);

        if ($path === '' || $path === '/') {
            return '/';
        }

        $path = '/'.ltrim($path, '/');

        return rtrim($path, '/') ?: '/';
    }

    public static function slugFromPath(string $path): string
    {
        if ($path === '/') {
            return 'home';
        }

        return trim(str_replace('/', '-', ltrim($path, '/')), '-') ?: 'page';
    }
}
