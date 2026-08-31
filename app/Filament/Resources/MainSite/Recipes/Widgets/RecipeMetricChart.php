<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Recipes\Widgets;

use App\Filament\Widgets\MetricChart;
use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeMetric;
use Illuminate\Database\Eloquent\Relations\HasMany;

abstract class RecipeMetricChart extends MetricChart
{
    /** @return HasMany<RecipeMetric, Recipe> */
    protected function metrics(): HasMany
    {
        assert($this->record instanceof Recipe);

        return $this->record->metrics();
    }
}
