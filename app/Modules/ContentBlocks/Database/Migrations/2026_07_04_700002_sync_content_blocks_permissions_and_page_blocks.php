<?php

use App\Models\Permission;
use App\Models\Role;
use App\Modules\ContentBlocks\Module as ContentBlocksModule;
use App\Services\Module\PermissionSyncService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $module = app(ContentBlocksModule::class);
        $sync = app(PermissionSyncService::class);

        $sync->syncModule($module);
        $sync->grantSuperAdmin($module);

        $permissionNames = collect($module->permissions())
            ->map(fn (array $permission) => $permission['module'].'.'.$permission['action'])
            ->all();

        $permissionIds = Permission::query()
            ->whereIn('name', $permissionNames)
            ->pluck('id')
            ->all();

        if ($permissionIds !== []) {
            foreach (['administrator', 'editor'] as $roleName) {
                $role = Role::query()->where('name', $roleName)->first();

                if ($role !== null) {
                    $role->permissions()->syncWithoutDetaching($permissionIds);
                }
            }
        }

        if (! Schema::hasTable('page_blocks')) {
            return;
        }

        $this->migrateBlockRows('page_blocks');
        $this->migrateBlockRows('page_master_blocks');
    }

    protected function migrateBlockRows(string $table): void
    {
        DB::table($table)
            ->where('block_type', 'featured-content')
            ->get()
            ->each(function (object $row) use ($table) {
                DB::table($table)->where('id', $row->id)->update([
                    'block_type' => 'content-block',
                    'config' => json_encode(['content_block_key' => 'featured-pages']),
                ]);
            });

        DB::table($table)
            ->where('block_type', 'latest-articles')
            ->get()
            ->each(function (object $row) use ($table) {
                DB::table($table)->where('id', $row->id)->update([
                    'block_type' => 'content-block',
                    'config' => json_encode(['content_block_key' => 'latest-articles']),
                ]);
            });
    }

    public function down(): void
    {
        // Permissions and migrated block rows are not reverted.
    }
};
