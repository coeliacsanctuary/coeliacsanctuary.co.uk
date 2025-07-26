<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\CheckForRouteRedirectAction;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AddRouteModelBindingFallbacksMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $route = $request->route();

        if ($route && $route->getName() !== 'fallback' && ! $route->getMissing()) {
            $route->missing(function (Request $request, ModelNotFoundException $exception) {
                $redirect = app(CheckForRouteRedirectAction::class)->handle($request->path());

                if ($redirect) {
                    $redirect->increment('hits');

                    return redirect($redirect->to, $redirect->status);
                }

                throw $exception;
            });
        }

        return $next($request);
    }
}
