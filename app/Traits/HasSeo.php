<?php

namespace App\Traits;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeo
{
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function saveSeo(array $data): void
    {
        $this->seoMeta()->updateOrCreate(
            ['seoable_id' => $this->id, 'seoable_type' => static::class],
            $data
        );
    }
}
