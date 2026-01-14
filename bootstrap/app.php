<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\Authenticate;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            SecurityHeaders::class,
        ]);

        // Apply to all web requests
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        // Use custom Authenticate middleware with flash messages
        $middleware->alias([
            'auth' => Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
