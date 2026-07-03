<?php

namespace App\Services\Public;

use App\Contracts\BlockConfigOptionsProvider;
use App\Contracts\BlockResolver;
use App\Framework\PublicBlock;
use App\Modules\PageManager\Models\Page;
use InvalidArgumentException;

class PublicBlockRegistry
{
    /** @var array<string, PublicBlock> */
    protected array $blocks = [];

    public function register(PublicBlock $block): void
    {
        $key = $block->key();

        if ($key === '') {
            throw new InvalidArgumentException('Public block key is required.');
        }

        $this->blocks[$key] = $block;
    }

    /**
     * @return array<string, PublicBlock>
     */
    public function all(): array
    {
        return $this->blocks;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function palette(): array
    {
        return array_values(array_map(
            fn (PublicBlock $block) => $block->toArray(),
            $this->blocks,
        ));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function paletteForAdmin(): array
    {
        return array_values(array_map(
            fn (PublicBlock $block) => $this->blockForAdmin($block),
            $this->blocks,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function defaultConfigFor(string $blockType): array
    {
        $block = $this->find($blockType);

        if ($block === null) {
            return [];
        }

        $config = [];

        foreach ($block->toArray()['config_schema'] ?? [] as $field) {
            if (! is_array($field)) {
                continue;
            }

            $key = (string) ($field['key'] ?? '');

            if ($key === '' || ! array_key_exists('default', $field)) {
                continue;
            }

            $config[$key] = $field['default'];
        }

        return $config;
    }

    public function find(string $key): ?PublicBlock
    {
        return $this->blocks[$key] ?? null;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function resolveProps(string $type, array $config, Page $page): array
    {
        $block = $this->find($type);

        if ($block === null) {
            return [];
        }

        $resolverClass = $block->toArray()['resolver'] ?? null;

        if (! is_string($resolverClass) || ! class_exists($resolverClass)) {
            return [];
        }

        $resolver = app($resolverClass);

        if (! $resolver instanceof BlockResolver) {
            return [];
        }

        return $resolver->resolve($config, $page);
    }

    public function componentFor(string $type): ?string
    {
        return $this->find($type)?->toArray()['component'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function blockForAdmin(PublicBlock $block): array
    {
        $data = $block->toArray();
        $schema = $data['config_schema'] ?? [];

        if (! is_array($schema)) {
            $data['config_schema'] = [];

            return $data;
        }

        $data['config_schema'] = array_map(
            fn (array $field) => $this->resolveSchemaFieldForAdmin($field),
            $schema,
        );

        return $data;
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>
     */
    protected function resolveSchemaFieldForAdmin(array $field): array
    {
        $providerClass = $field['optionsProvider'] ?? null;
        unset($field['optionsProvider']);

        if (($field['type'] ?? '') !== 'select') {
            return $field;
        }

        if (isset($field['options']) && is_array($field['options'])) {
            $field['options'] = $this->normalizeOptions($field['options']);

            return $field;
        }

        if (! is_string($providerClass) || ! class_exists($providerClass)) {
            $field['options'] = [];

            return $field;
        }

        $provider = app($providerClass);

        if (! $provider instanceof BlockConfigOptionsProvider) {
            $field['options'] = [];

            return $field;
        }

        $field['options'] = $provider->options();

        return $field;
    }

    /**
     * @param  array<int|string, mixed>  $options
     * @return array<int, array{value: string, label: string}>
     */
    protected function normalizeOptions(array $options): array
    {
        $normalized = [];

        foreach ($options as $key => $option) {
            if (is_array($option) && isset($option['value'], $option['label'])) {
                $normalized[] = [
                    'value' => (string) $option['value'],
                    'label' => (string) $option['label'],
                ];

                continue;
            }

            if (is_string($key)) {
                $normalized[] = [
                    'value' => (string) $key,
                    'label' => (string) $option,
                ];
            }
        }

        return $normalized;
    }
}
