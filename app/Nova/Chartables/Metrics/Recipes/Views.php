<?php

declare(strict_types=1);

namespace App\Nova\Chartables\Metrics\Recipes;

class Views extends RecipeMetric
{
    protected function column(): string
    {
        return 'page_views';
    }
}
