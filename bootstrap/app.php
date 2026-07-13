<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\Facades\Route;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Events\CommandStarting;
use App\Support\BlocksDestructiveDatabaseCommands;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(config('fyd.admin.middleware'))
                ->prefix(config('fyd.admin.prefix'))
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withCommands([
        \App\Modules\SiteReports\Console\PruneSiteVisitsCommand::class,
        \App\Console\Commands\ModuleInstallCommand::class,
        \App\Console\Commands\ModuleDisableCommand::class,
        \App\Console\Commands\ModuleEnableCommand::class,
        \App\Console\Commands\ModuleUninstallCommand::class,
        \App\Console\Commands\MakeModuleCommand::class,
        \App\Console\Commands\MakeThemeCommand::class,
    ])
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('site-reports:prune')->daily();
    })
    ->booting(function (Application $app): void {
        $app->make('events')->listen(
            CommandStarting::class,
            fn (CommandStarting $event) => $app->make(BlocksDestructiveDatabaseCommands::class)->handle($event),
        );
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            \App\Http\Middleware\UseRequestRootUrlInLocal::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\RejectBlockedIp::class,
            \App\Http\Middleware\TrackSiteVisit::class,
            \App\Http\Middleware\CachePublicResponse::class,
            \App\Http\Middleware\EnsurePublicSiteAvailable::class,
        ]);

        $middleware->redirectGuestsTo('/admin/login');
        $middleware->redirectUsersTo(fn () => auth()->user()?->hasPermission('dashboard.view')
            ? '/admin'
            : '/admin/access-pending');

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminAuthenticate::class,
            'admin.access' => \App\Http\Middleware\EnsureAdminPanelAccess::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'registration.enabled' => \App\Http\Middleware\EnsureRegistrationEnabled::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::redirectTo('admin.verification.notice'),
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            $limit = ini_get('post_max_size') ?: 'the configured server limit';
            $message = "The upload is too large. The server accepts requests up to {$limit}. Upload fewer files or reduce the file size.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return back()->with('error', $message);
        });
    })->create();
