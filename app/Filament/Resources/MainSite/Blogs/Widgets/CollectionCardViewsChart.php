<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs\Widgets;

class CollectionCardViewsChart extends BlogMetricChart
{
    protected ?string $heading = 'Collection Card Views';

    protected function column(): string
    {
        return 'collection_card_views';
    }
}
