<?php

namespace App\Modules\Dashboard\Controllers;

use App\Enums\ContentStatus;
use App\Framework\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Modules\Banners\Models\Banner;
use App\Modules\Pages\Models\Page;
use App\Modules\Posts\Models\Post;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Dashboard::class);

        $pageCount = Page::count();
        $postCount = Post::count();
        $bannerCount = Banner::count();
        $drafts = Page::where('status', ContentStatus::Draft)->count()
            + Post::where('status', ContentStatus::Draft)->count()
            + Banner::where('status', ContentStatus::Draft)->count();
        $published = Page::where('status', ContentStatus::Published)->count()
            + Post::where('status', ContentStatus::Published)->count()
            + Banner::where('status', ContentStatus::Published)->count();

        $stats = [
            'users' => User::count(),
            'pages' => $pageCount,
            'posts' => $postCount,
            'banners' => $bannerCount,
            'drafts' => $drafts,
            'published' => $published,
        ];

        $recentActivity = ActivityLog::with('user')->latest('created_at')->limit(10)->get();

        return view('dashboard::dashboard.index', compact('stats', 'recentActivity'));
    }
}
