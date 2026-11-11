<?php

declare(strict_types=1);

namespace Jpeters8889\CountryIcon;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Nova::serving(function (ServingNova $event): void {
            Nova::script('country-icon', __DIR__ . '/../dist/js/field.js');
            Nova::style('country-icon', __DIR__ . '/../dist/css/field.css');
        });
    }

    public function register(): void
    {
        //
    }
}
