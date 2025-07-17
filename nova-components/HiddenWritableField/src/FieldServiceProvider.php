<?php

declare(strict_types=1);

namespace Jpeters8889\HiddenWritableField;

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
        Nova::serving(function (ServingNova $event): void {
            Nova::mix('hidden-writable-field', __DIR__ . '/../dist/mix-manifest.json');
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
