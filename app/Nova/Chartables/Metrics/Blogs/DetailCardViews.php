<?php

declare(strict_types=1);

namespace App\Nova\Chartables\Metrics\Blogs;

class DetailCardViews extends BlogMetric
{
    protected function column(): string
    {
        return 'detail_card_views';
    }
}
