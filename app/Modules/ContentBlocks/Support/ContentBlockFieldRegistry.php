<?php

namespace App\Modules\ContentBlocks\Support;

class ContentBlockFieldRegistry
{
    /**
     * @return array<string, array{label: string, type: string, column?: string, relation?: string}>
     */
    public function all(): array
    {
        return [
            'title' => ['label' => 'Title', 'type' => 'text', 'column' => 'title'],
            'slug' => ['label' => 'Slug', 'type' => 'text', 'column' => 'slug'],
            'summary' => ['label' => 'Summary', 'type' => 'text', 'column' => 'summary'],
            'body' => ['label' => 'Body', 'type' => 'html', 'column' => 'body'],
            'content_type' => ['label' => 'Content Type', 'type' => 'content_type', 'column' => 'content_type'],
            'published_at' => ['label' => 'Published Date', 'type' => 'date', 'column' => 'published_at'],
            'author.name' => ['label' => 'Author', 'type' => 'text', 'relation' => 'author.name'],
            'featured_image' => ['label' => 'Featured Image', 'type' => 'media', 'relation' => 'featuredImage'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        return collect($this->all())
            ->mapWithKeys(fn (array $meta, string $key) => [$key => $meta['label']])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function sortableOptions(): array
    {
        return collect($this->all())
            ->filter(fn (array $meta) => isset($meta['column']))
            ->mapWithKeys(fn (array $meta, string $key) => [$key => $meta['label']])
            ->all();
    }

    public function has(string $field): bool
    {
        return array_key_exists($field, $this->all());
    }

    public function meta(string $field): ?array
    {
        return $this->all()[$field] ?? null;
    }

    public function defaultClass(string $field): string
    {
        $suffix = str_replace('.', '-', $field);

        return 'content-block__'.$suffix;
    }

    public function defaultId(string $blockKey, string $field): string
    {
        $suffix = str_replace('.', '-', $field);

        return 'content-block-'.$blockKey.'-'.$suffix;
    }

    public function defaultLabel(string $field): string
    {
        return $this->meta($field)['label'] ?? $field;
    }

    /**
     * @return list<string>
     */
    public function relationsFor(array $fieldKeys): array
    {
        $relations = [];

        foreach ($fieldKeys as $key) {
            $meta = $this->meta($key);

            if ($meta === null) {
                continue;
            }

            if ($key === 'author.name') {
                $relations[] = 'author';
            }

            if ($key === 'featured_image') {
                $relations[] = 'featuredImage';
            }
        }

        return array_values(array_unique($relations));
    }
}
