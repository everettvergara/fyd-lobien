<?php

namespace App\Modules\WebForms;

use App\Framework\PublicBlock;
use App\Modules\WebForms\Blocks\WebformBlockResolver;
use App\Modules\WebForms\Database\Seeders\DemoContactWebformSeeder;
use App\Modules\WebForms\Models\Webform;
use App\Modules\WebForms\Models\WebformSubmission;
use App\Modules\WebForms\Policies\WebformPolicy;
use App\Modules\WebForms\Policies\WebformSubmissionPolicy;
use App\Modules\WebForms\Services\WebformPageSyncService;
use App\Modules\WebForms\Support\WebformOptionsProvider;
use Illuminate\Support\Facades\Schema;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'WebForms';
    }

    public function isInstallable(): bool
    {
        return true;
    }

    public function policies(): array
    {
        return [
            Webform::class => WebformPolicy::class,
            WebformSubmission::class => WebformSubmissionPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('webforms', 'view', 'View Webforms'),
            $this->permissionEntry('webforms', 'create', 'Create Webforms'),
            $this->permissionEntry('webforms', 'edit', 'Edit Webforms'),
            $this->permissionEntry('webforms', 'delete', 'Delete Webforms'),
            $this->permissionEntry('webforms.submissions', 'view', 'View Webform Submissions'),
            $this->permissionEntry('webforms.submissions', 'delete', 'Delete Webform Submissions'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Webforms', 'admin.webforms.index', 'webforms.view', 'bi-ui-checks', sort: 10),
            $this->menuItem('Submissions', 'admin.webform-submissions.index', 'webforms.submissions.view', 'bi-inbox', sort: 20),
        ];
    }

    public function seeders(): array
    {
        return [DemoContactWebformSeeder::class];
    }

    public function uninstall(): void
    {
        if (! Schema::hasTable('webforms')) {
            return;
        }

        $pageSync = app(WebformPageSyncService::class);

        Webform::query()->each(function (Webform $webform) use ($pageSync) {
            $pageSync->removeWebformPage($webform);
        });
    }

    public function publicBlocks(): array
    {
        return [
            PublicBlock::make('webform')
                ->label('Web Form')
                ->icon('bi-ui-checks')
                ->module($this->name())
                ->resolver(WebformBlockResolver::class)
                ->component('WebformBlock')
                ->configSchema([
                    [
                        'key' => 'webform_slug',
                        'label' => 'Web Form',
                        'type' => 'select',
                        'required' => true,
                        'optionsProvider' => WebformOptionsProvider::class,
                    ],
                ]),
        ];
    }
}
