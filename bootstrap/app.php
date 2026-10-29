<?php

use App\Support\NotifikasiError;
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
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Error tak terduga (500-an) dilaporkan ke WA pengelola via Fonnte —
        // penyaringan & pembatasan ada di App\Support\NotifikasiError.
        $exceptions->reportable(fn (Throwable $e) => NotifikasiError::laporkan($e));
    })->create();
