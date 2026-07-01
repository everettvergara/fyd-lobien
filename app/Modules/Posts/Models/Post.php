<?php

namespace App\Modules\Posts\Models;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\User;
use App\Traits\HasSeo;
use App\Traits\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasSeo, Publishable, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'summary', 'excerpt', 'content', 'featured_image_id',
        'template', 'status', 'published_at', 'author_id',
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
}
