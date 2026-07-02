<?php

namespace App\Traits;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Builder;

trait Publishable
{
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [ContentStatus::Published, ContentStatus::Scheduled])
            ->where(function (Builder $q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }
}
