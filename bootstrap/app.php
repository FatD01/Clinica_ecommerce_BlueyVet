<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
         $middleware->redirectGuestsTo(function (Request $request) {
            // dd([
            //     'LOCATION' => 'bootstrap/app.php redirectGuestsTo',
            //     'Request Path' => $request->path(),
            //     'Is Admin Path?' => $request->is('admin/*'),
            // ]);

            // Si la URL es exactamente '/admin' O empieza con 'admin/'
            if ($request->is('admin') || $request->is('admin/*')) {
                return Filament::getLoginUrl();
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
