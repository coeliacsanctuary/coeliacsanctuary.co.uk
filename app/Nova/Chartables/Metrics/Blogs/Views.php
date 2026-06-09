<?php

declare(strict_types=1);

namespace App\Nova\Chartables\Metrics\Blogs;

class Views extends BlogMetric
{
    protected function column(): string
    {
        return 'page_views';
    }
}
