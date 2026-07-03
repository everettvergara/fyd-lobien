<?php

namespace App\Modules\HelloWorld;

use App\Modules\HelloWorld\Policies\HelloWorldPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'HelloWorld';
    }

    public function isInstallable(): bool
    {
        return true;
    }

    public function policies(): array
    {
        return [
            HelloWorldAccess::class => HelloWorldPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('hello_world', 'view', 'View Hello World'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Hello World', 'admin.hello-world.index', 'hello_world.view', 'bi-globe2', sort: 10),
        ];
    }
}
