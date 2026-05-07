<?php

declare(strict_types=1);

namespace App\Nova\Chartables\Metrics\Blogs;

class CollectionCardViews extends BlogMetric
{
    protected function column(): string
    {
        return 'collection_card_views';
    }
}
