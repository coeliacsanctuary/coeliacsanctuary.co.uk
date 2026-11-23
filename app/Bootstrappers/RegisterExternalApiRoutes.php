<?php

declare(strict_types=1);

namespace App\Bootstrappers;

use App\Http\Api\V1\Middleware\ExternalApiSourceMiddleware;
use App\Http\Api\V1\Middleware\RequireApiBasicAuthMiddleware;
use App\Http\Middleware\RequireWebBasicAuthMiddleware;
use Illuminate\Support\Facades\Route;

class RegisterExternalApiRoutes
{
    public static function handle(): void
    {
        Route::prefix('api/v1')
            ->middleware([
                'api',
                RequireApiBasicAuthMiddleware::class,
                ExternalApiSourceMiddleware::class,
            ])
            ->withoutMiddleware(RequireWebBasicAuthMiddleware::class)
            ->name('api.v1.')
            ->group(base_path('routes/api/v1.php'));
    }
}
