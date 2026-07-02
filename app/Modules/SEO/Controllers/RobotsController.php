<?php

namespace App\Modules\SEO\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SEO\Services\RobotsTxtService;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __construct(
        protected RobotsTxtService $robots,
    ) {}

    public function index(): Response
    {
        return response($this->robots->render(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
