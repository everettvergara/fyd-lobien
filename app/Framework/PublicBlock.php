<?php

namespace App\Framework;

class PublicBlock
{
    /** @var array<string, mixed> */
    protected array $attributes = [];

    public static function make(string $key): self
    {
        $block = new self;
        $block->attributes['key'] = $key;

        return $block;
    }

    public function label(string $label): self
    {
        $this->attributes['label'] = $label;

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->attributes['icon'] = $icon;

        return $this;
    }

    public function module(string $module): self
    {
        $this->attributes['module'] = $module;

        return $this;
    }

    /**
     * @param  class-string  $resolver
     */
    public function resolver(string $resolver): self
    {
        $this->attributes['resolver'] = $resolver;

        return $this;
    }

    public function component(string $component): self
    {
        $this->attributes['component'] = $component;

        return $this;
    }

    /**
     * @param  array<int, array<string, mixed>>  $schema
     */
    public function configSchema(array $schema): self
    {
        $this->attributes['config_schema'] = $schema;

        return $this;
    }

    public function key(): string
    {
        return (string) ($this->attributes['key'] ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
