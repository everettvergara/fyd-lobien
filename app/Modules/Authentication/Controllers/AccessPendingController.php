<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AccessPendingController extends Controller
{
    public function show(): View
    {
        return view('authentication::auth.access-pending');
    }
}
