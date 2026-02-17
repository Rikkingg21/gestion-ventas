<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registrar alias para middlewares
        $middleware->alias([
            'client' => \App\Http\Middleware\ClientMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'staff' => \App\Http\Middleware\StaffMiddleware::class,
        ]);

        // Personalizar redirección para invitados
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin/*') || $request->routeIs('admin.*')) {
                return route('admin.login');
            }
            if ($request->is('staff/*') || $request->routeIs('staff.*')) {
                return route('staff.login');
            }
            if ($request->is('client/*') || $request->routeIs('client.*')) {
                return route('client.login');
            }
            return route('home');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
