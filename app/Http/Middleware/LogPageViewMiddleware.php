<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Journey\QueuePageViewAction;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class LogPageViewMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->shouldTrack($request)) {
            app(QueuePageViewAction::class)->handle(
                $request->session()->getId(),
                $request->path(),
                $request->route()?->getName(),
            );

            $request->headers->set('X-Journey-Token', Crypt::encrypt([
                'session_id' => $request->session()->getId(),
                'path' => $request->path(),
            ]));
        }

        return $next($request);
    }

    protected function shouldTrack(Request $request): bool
    {
        if (config()->boolean('coeliac.journey.enabled') === false) {
            return false;
        }

        if ($request->method() !== 'GET') {
            return false;
        }

        if (in_array($request->path(), config()->array('journey.dont-track'))) {
            return false;
        }

        return ! (in_array($request->route()?->uri(), config()->array('journey.dont-track')));
    }
}
