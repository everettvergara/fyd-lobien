<?php

namespace App\Modules\HelloWorld\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HelloWorld\HelloWorldAccess;
use Illuminate\View\View;

class HelloWorldController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', HelloWorldAccess::class);

        return view('helloworld::hello-world.index');
    }
}
