<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\EatingOut\EateryRecommendation;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class PlaceRequests extends Value
{
    public $icon = 'document-add';

    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->result(EateryRecommendation::query()->withoutGlobalScopes()->where('completed', false)->count());
    }

    public function name()
    {
        return 'New Place Requests';
    }
}
