<?php

declare(strict_types=1);

namespace Jpeters8889\Body;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        Nova::serving(function (ServingNova $event): void {
            Nova::script('body', __DIR__ . '/../dist/js/field.js');
            Nova::style('body', __DIR__ . '/../dist/css/field.css');
        });
    }

    /**
     * @return void
     */
    public function register(): void
    {
        //
    }
}
