<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Home', [
            'hero' => [
                'title' => 'Welcome to FYD CMS',
                'subtitle' => 'Your professional corporate website platform',
                'description' => 'A lightweight, modular, and maintainable content management system built with Laravel and Bootstrap.',
                'buttonText' => 'Learn More',
                'buttonUrl' => '#about',
            ],
        ]);
    }
}
