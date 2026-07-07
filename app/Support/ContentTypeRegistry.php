<?php

namespace App\Support;

use App\Modules\Content\Models\ContentType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ContentTypeRegistry
{
    protected const CACHE_KEY = 'content-types.registry';

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            if (Schema::hasTable('content_types')) {
                return ContentType::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('label')
                    ->get()
                    ->mapWithKeys(fn (ContentType $type) => [
                        $type->key => [
                            'label' => $type->label,
                            'description' => $type->description,
                            'icon' => $type->icon,
                            'slug' => $type->slug,
                        ],
                    ])
                    ->all();
            }

            return config('content-types', []);
        });
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->all());
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function label(string $key): string
    {
        return $this->all()[$key]['label'] ?? $key;
    }

    public function icon(string $key): string
    {
        return $this->all()[$key]['icon'] ?? 'bi-file-earmark';
    }

    public function slug(string $key): ?string
    {
        $slug = $this->all()[$key]['slug'] ?? null;

        return is_string($slug) && $slug !== '' ? $slug : null;
    }

    public function badgeHtml(string $key): string
    {
        $label = e($this->label($key));

        return sprintf(
            '<span class="badge bg-secondary-subtle text-secondary"><i class="%s me-1"></i>%s</span>',
            e(AdminIcon::solid($this->icon($key))),
            $label,
        );
    }

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        return collect($this->all())
            ->mapWithKeys(fn (array $type, string $key) => [$key => $type['label']])
            ->all();
    }

    /**
     * @return array{key: string, label: string}
     */
    public function dto(string $key): array
    {
        return [
            'key' => $key,
            'label' => $this->label($key),
        ];
    }
}
