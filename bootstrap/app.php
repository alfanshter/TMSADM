<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withCommands([
        \App\Console\Commands\ClearTmpPhotos::class
    ])
    ->withSchedule(function (Schedule $schedule) {
        // Contoh schedule: hapus tmp_photos tiap 1 jam
        $schedule->command('photos:clear-tmp')->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
