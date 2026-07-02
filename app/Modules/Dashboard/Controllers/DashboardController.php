<?php

namespace App\Modules\Dashboard\Controllers;

use App\Framework\Dashboard;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Dashboard::class);

        return view('dashboard::dashboard.index');
    }
}
