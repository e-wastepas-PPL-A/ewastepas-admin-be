<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseJson;
use App\Http\Controllers\ResponseResource;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SanctumAbilityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$abilities)
    {
        if (!$request->user() || !$request->user()->currentAccessToken()) {
            throw new AuthenticationException();
        }

        $hasAbility = false;

        foreach ($abilities as $ability) {
            if ($request->user()->tokenCan($ability)) {
                $hasAbility = true;
                break;
            }
        }

        if (!$hasAbility) {
            return ResponseJson::forbidenResponse();
        }

        return $next($request);
    }
}
