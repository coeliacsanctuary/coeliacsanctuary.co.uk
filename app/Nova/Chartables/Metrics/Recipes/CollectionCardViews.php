<?php

declare(strict_types=1);

namespace App\Nova\Chartables\Metrics\Recipes;

class CollectionCardViews extends RecipeMetric
{
    protected function column(): string
    {
        return 'collection_card_views';
    }
}
