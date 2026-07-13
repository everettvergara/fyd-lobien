<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Modules\Content\Services\ContentTypeSyncService;
use Illuminate\Database\Seeder;

/**
 * Seeds Lobien static-blocks content (sidebar link lists, etc.).
 *
 * @see config/content-types.php
 */
class LobienStaticBlocksSeeder extends Seeder
{
    public function run(): void
    {
        app(ContentTypeSyncService::class)->syncFromConfig();

        $admin = User::query()->where('email', 'admin@fyd.local')->first()
            ?? User::query()->orderBy('id')->first();

        if ($admin === null) {
            $this->command?->error('No admin user found. Run AuthenticationSeeder first.');

            return;
        }

        Content::updateOrCreate(
            ['slug' => 'sidebar-lrg-bulletin'],
            [
                'content_type' => 'static-blocks',
                'title' => 'SIDEBAR-LRG-BULLETIN',
                'summary' => null,
                'body' => <<<'HTML'
<ul>
  <li><a href="/articles">Articles</a></li>
  <li><a href="/videos">Videos</a></li>
  <li><a href="/property-tours">Property tours</a></li>
  <li><a href="/downloadable">Downloadables</a></li>
</ul>
HTML,
                'status' => ContentStatus::Published,
                'published_at' => now(),
                'author_id' => $admin->id,
            ],
        );
    }
}
