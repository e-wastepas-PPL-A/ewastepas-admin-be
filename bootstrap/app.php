<?php

use App\Http\Middleware\{MemberAccessLogin, SanctumAbilityMiddleware, Authenticate, CheckStoreActivationMiddleware};
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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'ability' => SanctumAbilityMiddleware::class,
            'memberAccessLogin' => MemberAccessLogin::class,
            'auth' => Authenticate::class,
            'checkStoreActivation' => CheckStoreActivationMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
