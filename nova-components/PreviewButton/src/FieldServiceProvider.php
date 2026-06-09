<?php

declare(strict_types=1);

namespace Jpeters8889\PreviewButton;

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
            Nova::script('preview-button', __DIR__ . '/../dist/js/field.js');
            Nova::style('preview-button', __DIR__ . '/../dist/css/field.css');
        });
    }

    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix('/nova-vendor/preview-button')
            ->group(function (): void {
                Route::post('store', PreviewStoreController::class)->name('nova-preview.store');
            });
    }

    public function register(): void
    {
        //
    }
}
