<?php

namespace App\Modules\Content\Models;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\User;
use App\Traits\HasSeo;
use App\Traits\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use HasSeo, Publishable, SoftDeletes;

    protected $fillable = [
        'content_type', 'title', 'slug', 'summary', 'body', 'url_link',
        'featured_image_id', 'attachment_id', 'status', 'published_at', 'author_id', 'public_page_path',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
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

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'attachment_id');
    }

    public function galleryImages(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'content_media')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }
}
