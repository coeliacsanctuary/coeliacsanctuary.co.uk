<?php

declare(strict_types=1);

namespace Jpeters8889\EateryCollectionsQueryBuilder\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jpeters8889\EateryCollectionsQueryBuilder\EateryCollectionsQueryBuilder;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Symfony\Component\HttpFoundation\Response;

class Authorize
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tool = collect(Nova::registeredTools())->first([$this, 'matchesTool']);

        if ($tool === null) {
            abort(404);
        }

        if ( ! $tool->authorize($request)) {
            abort(403);
        }

        return $next($request);
    }

    public function matchesTool(Tool $tool): bool
    {
        return $tool instanceof EateryCollectionsQueryBuilder;
    }
}
