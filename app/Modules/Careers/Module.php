<?php

namespace App\Modules\Careers;

use App\Framework\PublicBlock;
use App\Modules\Careers\Blocks\CareersListingBlockResolver;
use App\Modules\Careers\Database\Seeders\CareerPublicPageSeeder;
use App\Modules\Careers\Database\Seeders\DemoCareerSeeder;
use App\Modules\Careers\Models\CareerApplication;
use App\Modules\Careers\Models\CareerJob;
use App\Modules\Careers\Policies\CareerApplicationPolicy;
use App\Modules\Careers\Policies\CareerJobPolicy;
use App\Modules\Careers\Services\CareerPageSyncService;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Careers';
    }

    public function isInstallable(): bool
    {
        return true;
    }

    public function policies(): array
    {
        return [
            CareerJob::class => CareerJobPolicy::class,
            CareerApplication::class => CareerApplicationPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('careers.jobs', 'view', 'View Job Listings'),
            $this->permissionEntry('careers.jobs', 'create', 'Create Job Listings'),
            $this->permissionEntry('careers.jobs', 'edit', 'Edit Job Listings'),
            $this->permissionEntry('careers.jobs', 'delete', 'Delete Job Listings'),
            $this->permissionEntry('careers.applications', 'view', 'View Applications'),
            $this->permissionEntry('careers.applications', 'delete', 'Delete Applications'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Job Listings', 'admin.career-jobs.index', 'careers.jobs.view', 'bi-briefcase', sort: 10),
            $this->menuItem('Applications', 'admin.career-applications.index', 'careers.applications.view', 'bi-inbox', sort: 20),
        ];
    }

    public function seeders(): array
    {
        return [DemoCareerSeeder::class, CareerPublicPageSeeder::class];
    }

    public function uninstall(): void
    {
        app(CareerPageSyncService::class)->removeIndexPageIfManaged();
    }

    public function publicBlocks(): array
    {
        return [
            PublicBlock::make('careers-listing')
                ->label('Careers Listing')
                ->icon('bi-briefcase')
                ->module($this->name())
                ->resolver(CareersListingBlockResolver::class)
                ->component('CareersListingBlock')
                ->configSchema([
                    [
                        'key' => 'per_page',
                        'label' => 'Jobs per page',
                        'type' => 'number',
                        'default' => CareersListingBlockResolver::PER_PAGE,
                        'min' => 1,
                        'max' => 48,
                    ],
                ]),
        ];
    }
}
