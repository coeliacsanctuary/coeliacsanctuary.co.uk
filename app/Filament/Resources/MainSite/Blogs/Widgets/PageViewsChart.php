<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs\Widgets;

class PageViewsChart extends BlogMetricChart
{
    protected ?string $heading = 'Views';

    protected function column(): string
    {
        return 'page_views';
    }
}
