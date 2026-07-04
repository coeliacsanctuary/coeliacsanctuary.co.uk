<?php

declare(strict_types=1);

namespace Jpeters8889\EateryRecommendationEligibility;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Nova::serving(function (ServingNova $event): void {
            Nova::mix('eatery-recommendation-eligibility', __DIR__ . '/../dist/mix-manifest.json');
        });
    }
}
