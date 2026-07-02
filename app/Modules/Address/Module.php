<?php

namespace App\Modules\Address;

use App\Modules\Address\Models\City;
use App\Modules\Address\Models\Province;
use App\Modules\Address\Policies\CityPolicy;
use App\Modules\Address\Policies\ProvincePolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Address';
    }

    public function policies(): array
    {
        return [
            Province::class => ProvincePolicy::class,
            City::class => CityPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('provinces', 'view', 'View Provinces'),
            $this->permissionEntry('provinces', 'create', 'Create Provinces'),
            $this->permissionEntry('provinces', 'edit', 'Edit Provinces'),
            $this->permissionEntry('provinces', 'delete', 'Delete Provinces'),
            $this->permissionEntry('cities', 'view', 'View Cities'),
            $this->permissionEntry('cities', 'create', 'Create Cities'),
            $this->permissionEntry('cities', 'edit', 'Edit Cities'),
            $this->permissionEntry('cities', 'delete', 'Delete Cities'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Provinces', 'admin.provinces.index', 'provinces.view', 'bi-geo-alt', 'Reference Data', sort: 10),
            $this->menuItem('Cities', 'admin.cities.index', 'cities.view', 'bi-building', 'Reference Data', sort: 20),
        ];
    }
}
