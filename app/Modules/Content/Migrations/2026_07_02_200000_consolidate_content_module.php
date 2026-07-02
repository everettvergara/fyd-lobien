<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('page_sections');

        if (Schema::hasTable('pages')) {
            if (Schema::hasColumn('pages', 'parent_id')) {
                Schema::table('pages', function (Blueprint $table) {
                    $table->dropForeign(['parent_id']);
                });
            }

            Schema::table('pages', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('pages', 'parent_id')) {
                    $columns[] = 'parent_id';
                }
                if (Schema::hasColumn('pages', 'template')) {
                    $columns[] = 'template';
                }
                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });

            Schema::rename('pages', 'contents');
        }

        if (Schema::hasTable('contents')) {
            if (Schema::hasColumn('contents', 'content') && ! Schema::hasColumn('contents', 'body')) {
                Schema::table('contents', function (Blueprint $table) {
                    $table->renameColumn('content', 'body');
                });
            }

            if (! Schema::hasColumn('contents', 'content_type')) {
                Schema::table('contents', function (Blueprint $table) {
                    $table->string('content_type')->default('page')->after('slug');
                });
            }

            DB::table('contents')->whereNull('content_type')->orWhere('content_type', '')->update(['content_type' => 'page']);

            $this->updateSeoMorphType('App\\Modules\\Pages\\Models\\Page', 'App\\Modules\\Content\\Models\\Content');
        }

        $this->migratePostsToContents();

        Schema::dropIfExists('posts');

        if (Schema::hasTable('permissions')) {
            $this->migratePermissions();
        }
    }

    protected function migratePermissions(): void
    {
        $contentByName = DB::table('permissions')
            ->where('module', 'content')
            ->pluck('id', 'name');

        foreach (DB::table('permissions')->where('module', 'pages')->get() as $permission) {
            $newName = str_replace('pages.', 'content.', $permission->name);
            $existingContentId = $contentByName[$newName] ?? null;

            if ($existingContentId) {
                $this->reassignPermissionRoles((int) $permission->id, (int) $existingContentId);
                DB::table('permissions')->where('id', $permission->id)->delete();

                continue;
            }

            DB::table('permissions')->where('id', $permission->id)->update([
                'module' => 'content',
                'name' => $newName,
                'display_name' => str_replace('Pages', 'Content', $permission->display_name),
            ]);

            $contentByName[$newName] = $permission->id;
        }

        $postPermissionIds = DB::table('permissions')->where('module', 'posts')->pluck('id');
        if ($postPermissionIds->isNotEmpty()) {
            DB::table('permission_role')->whereIn('permission_id', $postPermissionIds)->delete();
            DB::table('permissions')->whereIn('id', $postPermissionIds)->delete();
        }
    }

    protected function reassignPermissionRoles(int $fromPermissionId, int $toPermissionId): void
    {
        $roleIds = DB::table('permission_role')
            ->where('permission_id', $fromPermissionId)
            ->pluck('role_id');

        foreach ($roleIds as $roleId) {
            $exists = DB::table('permission_role')
                ->where('permission_id', $toPermissionId)
                ->where('role_id', $roleId)
                ->exists();

            if (! $exists) {
                DB::table('permission_role')->insert([
                    'permission_id' => $toPermissionId,
                    'role_id' => $roleId,
                ]);
            }
        }

        DB::table('permission_role')->where('permission_id', $fromPermissionId)->delete();
    }

    protected function migratePostsToContents(): void
    {
        if (! Schema::hasTable('posts') || ! Schema::hasTable('contents')) {
            return;
        }

        $bodyColumn = Schema::hasColumn('posts', 'content') ? 'content' : 'body';

        foreach (DB::table('posts')->orderBy('id')->get() as $post) {
            $slug = (string) $post->slug;
            if (DB::table('contents')->where('slug', $slug)->exists()) {
                $slug = $slug.'-article';
                $suffix = 2;
                while (DB::table('contents')->where('slug', $slug)->exists()) {
                    $slug = $post->slug.'-article-'.$suffix;
                    $suffix++;
                }
            }

            $contentId = DB::table('contents')->insertGetId([
                'content_type' => 'article',
                'title' => $post->title,
                'slug' => $slug,
                'summary' => $post->summary ?? $post->excerpt ?? null,
                'body' => $post->{$bodyColumn} ?? null,
                'featured_image_id' => $post->featured_image_id ?? null,
                'status' => $post->status,
                'published_at' => $post->published_at,
                'author_id' => $post->author_id,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
                'deleted_at' => $post->deleted_at ?? null,
            ]);

            if (Schema::hasTable('seo_meta')) {
                DB::table('seo_meta')
                    ->where('seoable_type', 'App\\Modules\\Posts\\Models\\Post')
                    ->where('seoable_id', $post->id)
                    ->update([
                        'seoable_type' => 'App\\Modules\\Content\\Models\\Content',
                        'seoable_id' => $contentId,
                    ]);
            }
        }
    }

    protected function updateSeoMorphType(string $fromType, string $toType): void
    {
        if (! Schema::hasTable('seo_meta')) {
            return;
        }

        DB::table('seo_meta')
            ->where('seoable_type', $fromType)
            ->update(['seoable_type' => $toType]);
    }

    public function down(): void
    {
        // Irreversible consolidation migration.
    }
};
