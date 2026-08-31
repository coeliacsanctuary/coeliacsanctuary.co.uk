<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Recipes\Widgets;

class DetailCardViewsChart extends RecipeMetricChart
{
    protected ?string $heading = 'Detail Card Views';

    protected function column(): string
    {
        return 'detail_card_views';
    }
}
