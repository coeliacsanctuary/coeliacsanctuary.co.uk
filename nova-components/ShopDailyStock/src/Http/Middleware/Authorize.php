<?php

declare(strict_types=1);

namespace Jpeters8889\ShopDailyStock\Http\Middleware;

use Closure;
use Jpeters8889\ShopDailyStock\ShopDailyStock;
use Laravel\Nova\Nova;

class Authorize
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure(\Illuminate\Http\Request):mixed  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        $tool = collect(Nova::registeredTools())->first([$this, 'matchesTool']);

        return optional($tool)->authorize($request) ? $next($request) : abort(403);
    }

    /**
     * @param  \Laravel\Nova\Tool  $tool
     * @return bool
     */
    public function matchesTool($tool)
    {
        return $tool instanceof ShopDailyStock;
    }
}
