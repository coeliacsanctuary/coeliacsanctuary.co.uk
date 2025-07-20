<?php

declare(strict_types=1);

namespace Jpeters8889\EateryLocationSearch;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function (): void {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event): void {
            Nova::mix('eatery-location-search', __DIR__ . '/../dist/mix-manifest.json');
        });
    }

    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix('nova-vendor/eatery-location-search')
            ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
