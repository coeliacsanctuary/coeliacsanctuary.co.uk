<?php

declare(strict_types=1);

use App\Bootstrappers\RegisterExceptionHandler;
use App\Bootstrappers\RegisterExternalApiRoutes;
use App\Bootstrappers\RegisterLocalDevRoutes;
use App\Bootstrappers\RegisterMiddlewareConfiguration;
use Illuminate\Foundation\Application;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function (): void {
            RegisterExternalApiRoutes::handle();

            if (config('app.env') === 'local') {
                RegisterLocalDevRoutes::handle();
            }
        }
    )
    ->withMiddleware(RegisterMiddlewareConfiguration::handle(...))
    ->withExceptions(RegisterExceptionHandler::handle(...))
    ->create();
