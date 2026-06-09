<?php

declare(strict_types=1);

namespace Jpeters8889\RelatedRecipesSearch;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function (): void {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event): void {
            Nova::mix('related-recipes-search', __DIR__ . '/../dist/mix-manifest.json');
        });
    }

    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix('nova-vendor/related-recipes-search')
            ->group(__DIR__ . '/../routes/api.php');
    }

    public function register(): void
    {
        //
    }
}
