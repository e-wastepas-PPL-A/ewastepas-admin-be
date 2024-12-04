<?php

use App\Helpers\ResponseJson;
use App\Http\Middleware\{MemberAccessLogin, SanctumAbilityMiddleware, Authenticate, CheckStoreActivationMiddleware};
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(static fn() => throw new UnauthorizedHttpException(config('app.name')));
        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->hasHeader('Authorization')) {
                throw new UnauthorizedHttpException(config('app.name'));
            }

            throw new NotFoundHttpException();
        });

        $middleware->alias([
            'ability' => SanctumAbilityMiddleware::class,
            // 'auth' => Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (UnauthorizedHttpException $exception, Request $request) {
            return ResponseJson::unauthorizeResponse("Unauthorized.");
        });
    })->create();
