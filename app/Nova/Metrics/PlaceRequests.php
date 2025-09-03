<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\EatingOut\EateryRecommendation;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use SaintSystems\Nova\LinkableMetrics\LinkableValue;

class PlaceRequests extends Value
{
    use LinkableValue;

    public $icon = 'document-add';

    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->result(EateryRecommendation::query()
            ->withoutGlobalScopes()
            ->whereNot('email', 'alisondwheatley@gmail.com')
            ->where('completed', false)->count());
    }

    public function name()
    {
        return 'New Place Requests';
    }
}
