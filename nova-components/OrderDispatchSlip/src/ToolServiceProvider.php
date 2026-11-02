<?php

declare(strict_types=1);

namespace Jpeters8889\OrderDispatchSlip;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Jpeters8889\OrderDispatchSlip\Http\Middleware\Authorize;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Http\Middleware\Authenticate;
use Laravel\Nova\Nova;

class ToolServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function (): void {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event): void {
            //
        });
    }

    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Nova::router(['nova', Authenticate::class, Authorize::class], 'order-dispatch-slip')
            ->group(__DIR__ . '/../routes/inertia.php');

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/order-dispatch-slip')
            ->group(__DIR__ . '/../routes/api.php');
    }

    public function register(): void
    {
        //
    }
}
