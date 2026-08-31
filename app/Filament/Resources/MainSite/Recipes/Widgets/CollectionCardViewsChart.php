<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Recipes\Widgets;

class CollectionCardViewsChart extends RecipeMetricChart
{
    protected ?string $heading = 'Collection Card Views';

    protected function column(): string
    {
        return 'collection_card_views';
    }
}
