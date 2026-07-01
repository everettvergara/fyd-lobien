<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard.index', [
            'stats' => [
                'users' => 0,
                'pages' => 0,
                'posts' => 0,
                'banners' => 0,
                'drafts' => 0,
                'published' => 0,
            ],
        ]);
    }
}
