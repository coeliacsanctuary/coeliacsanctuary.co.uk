<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Middleware;

use Closure;
use Illuminate\Http\Request;

class ExternalApiSourceMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ( ! $request->hasHeader('X-Coeliac-Source')) {
            abort(403);
        }

        return $next($request);
    }
}
