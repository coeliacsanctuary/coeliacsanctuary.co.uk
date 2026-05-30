<?php

declare(strict_types=1);

namespace Jpeters8889\EateryCollectionsQueryBuilder;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Jpeters8889\EateryCollectionsQueryBuilder\Http\Middleware\Authorize;
use Laravel\Nova\Events\ServingNova;
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

        Nova::router(['nova', 'nova.auth', Authorize::class], 'eatery-collections-query-builder')
            ->group(__DIR__ . '/../routes/inertia.php');

        Route::middleware(['nova', 'nova.auth', Authorize::class])
            ->prefix('nova-vendor/eatery-collections-query-builder')
            ->group(__DIR__ . '/../routes/api.php');
    }

    public function register(): void
    {
        //
    }
}
