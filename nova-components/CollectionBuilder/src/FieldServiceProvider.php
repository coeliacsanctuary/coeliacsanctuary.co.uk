<?php

declare(strict_types=1);

namespace Jpeters8889\CollectionBuilder;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Nova::serving(function (ServingNova $event): void {
            Nova::mix('collection-builder', __DIR__ . '/../dist/mix-manifest.json');
        });
    }

    public function register(): void
    {
        //
    }
}
