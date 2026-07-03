<?php

namespace App\Framework;

use Illuminate\Support\Facades\Gate;

class ModuleRegistry
{
    /** @var array<int, Module> */
    protected array $modules = [];

    public function register(Module $module): void
    {
        $this->modules[$module->name()] = $module;
    }

    /**
     * @return array<int, Module>
     */
    public function all(): array
    {
        return array_values($this->modules);
    }

    public function reset(): void
    {
        $this->modules = [];
    }

    public function bootPolicies(): void
    {
        foreach ($this->modules as $module) {
            foreach ($module->policies() as $model => $policy) {
                Gate::policy($model, $policy);
            }
        }
    }

    public function bootMenus(MenuRegistry $menuRegistry): void
    {
        foreach ($this->modules as $module) {
            foreach ($module->menuItems() as $item) {
                $menuRegistry->register($item);
            }
        }
    }

    public function bootPublicBlocks(\App\Services\Public\PublicBlockRegistry $registry): void
    {
        foreach ($this->modules as $module) {
            foreach ($module->publicBlocks() as $block) {
                $registry->register($block);
            }
        }
    }
}
