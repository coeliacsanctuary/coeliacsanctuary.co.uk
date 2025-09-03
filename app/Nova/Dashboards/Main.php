<?php

declare(strict_types=1);

namespace App\Nova\Dashboards;

use App\Nova\Chartables\EateryRatings;
use App\Nova\Chartables\EmailsSent;
use App\Nova\Metrics\Comments;
use App\Nova\Metrics\PlaceRequests;
use App\Nova\Metrics\Ratings;
use Jpeters8889\ApexCharts\ApexChart;
use Laravel\Nova\Dashboards\Main as Dashboard;

/**
 * @codeCoverageIgnore
 */
class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            Comments::make()->route('nova.pages.index', ['resource' => 'comments']),
            Ratings::make()->route('nova.pages.index', ['resource' => 'reviews']),
            PlaceRequests::make()->route('nova.pages.index', ['resource' => 'place-recomendations']),
            ApexChart::make(EmailsSent::class)->fullWidth(),
            ApexChart::make(EateryRatings::class)->fullWidth(),
        ];
    }
}
