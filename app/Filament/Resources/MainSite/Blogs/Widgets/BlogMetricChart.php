<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs\Widgets;

use App\Filament\Widgets\MetricChart;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogMetric;
use Illuminate\Database\Eloquent\Relations\HasMany;

abstract class BlogMetricChart extends MetricChart
{
    /** @return HasMany<BlogMetric, Blog> */
    protected function metrics(): HasMany
    {
        assert($this->record instanceof Blog);

        return $this->record->metrics();
    }
}
