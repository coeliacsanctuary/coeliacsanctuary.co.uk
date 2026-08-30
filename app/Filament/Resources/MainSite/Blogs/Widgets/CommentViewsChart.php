<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs\Widgets;

class CommentViewsChart extends BlogMetricChart
{
    protected ?string $heading = 'Comment Views';

    protected function column(): string
    {
        return 'page_comment_views';
    }
}
