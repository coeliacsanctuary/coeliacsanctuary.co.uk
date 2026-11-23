<?php

declare(strict_types=1);

namespace App\Bootstrappers;

use App\Http\Middleware\AddRouteModelBindingFallbacksMiddleware;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RequireWebBasicAuthMiddleware;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use JourneyTracker\JourneyTrackerLaravel\Http\Middleware\LogPageViewMiddleware;

class RegisterMiddlewareConfiguration
{
    public static function handle(Middleware $middleware): Middleware
    {
        $middleware->web(
            append: [
                HandleInertiaRequests::class,
                LogPageViewMiddleware::class,
            ],
            prepend: [
                RequireWebBasicAuthMiddleware::class,
                AddRouteModelBindingFallbacksMiddleware::class,
            ]
        );

        $middleware->api(prepend: RequireWebBasicAuthMiddleware::class);

        $middleware->redirectGuestsTo(function (Request $request): string {
            if (Str::contains($request->url(), 'my-travel-cards.')) {
                return route('my-travel-cards.login.show');
            }

            return route('nova.pages.login');
        });

        return $middleware;
    }
}
