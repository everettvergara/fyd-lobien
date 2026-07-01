<?php

namespace App\Modules\SEO\Services;

use App\Support\SeoFields;
use Illuminate\Database\Eloquent\Model;

class SeoService
{
    /**
     * @return array<int, string>
     */
    public function fieldKeys(): array
    {
        return array_keys(SeoFields::rules());
    }

    public function extract(array $data): array
    {
        return SeoFields::extract($data);
    }

    public function rules(): array
    {
        return SeoFields::rules();
    }

    public function copyTo(Model $source, Model $target): void
    {
        if (! method_exists($source, 'seoMeta') || ! method_exists($target, 'saveSeo')) {
            return;
        }

        $source->loadMissing('seoMeta');

        if ($source->seoMeta) {
            $target->saveSeo($source->seoMeta->only($this->fieldKeys()));
        }
    }
}
