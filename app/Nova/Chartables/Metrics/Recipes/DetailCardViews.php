<?php

declare(strict_types=1);

namespace App\Nova\Chartables\Metrics\Recipes;

class DetailCardViews extends RecipeMetric
{
    protected function column(): string
    {
        return 'detail_card_views';
    }
}
