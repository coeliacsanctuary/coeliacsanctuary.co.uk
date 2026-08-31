<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Recipes\Widgets;

class CommentViewsChart extends RecipeMetricChart
{
    protected ?string $heading = 'Comment Views';

    protected function column(): string
    {
        return 'page_comment_views';
    }
}
