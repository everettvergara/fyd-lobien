<?php

namespace App\Providers;

use App\Models\Media;
use App\Models\Role;
use App\Models\User;
use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Policies\BannerPolicy;
use App\Modules\Media\Policies\MediaPolicy;
use App\Modules\Menus\Models\Menu;
use App\Modules\Menus\Policies\MenuPolicy;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Policies\PagePolicy;
use App\Modules\Posts\Models\Post;
use App\Modules\Posts\Policies\PostPolicy;
use App\Modules\Roles\Policies\RolePolicy;
use App\Modules\Users\Policies\UserPolicy;
use App\Services\MailConfigService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Banner::class, BannerPolicy::class);
        Gate::policy(Menu::class, MenuPolicy::class);
        Gate::policy(Media::class, MediaPolicy::class);

        app(MailConfigService::class)->apply();
    }
}
