<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Redirects\CheckForRouteRedirectAction;
use App\Actions\Redirects\HandleRedirectResponseAction;
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
                    return app(HandleRedirectResponseAction::class)->handle($redirect);
                }

                throw $exception;
            });
        }

        return $next($request);
    }
}
