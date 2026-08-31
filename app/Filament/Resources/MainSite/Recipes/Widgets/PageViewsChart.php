<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Recipes\Widgets;

class PageViewsChart extends RecipeMetricChart
{
    protected ?string $heading = 'Views';

    protected function column(): string
    {
        return 'page_views';
    }
}
