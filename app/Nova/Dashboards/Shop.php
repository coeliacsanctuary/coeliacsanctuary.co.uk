<?php

declare(strict_types=1);

namespace App\Nova\Dashboards;

use App\Nova\Chartables\BasketOrders;
use App\Nova\Chartables\Income;
use App\Nova\Chartables\PaymentProviders;
use App\Nova\Chartables\ProductSales;
use Jpeters8889\ApexCharts\ApexChart;
use Laravel\Nova\Dashboard;

class Shop extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            ApexChart::make(BasketOrders::class)->fullWidth()->withCustomDateRange()->fixedHeight(),
            ApexChart::make(Income::class)->width('1/2')->withCustomDateRange(),
            ApexChart::make(PaymentProviders::class)->width('1/2')->withCustomDateRange(),
            ApexChart::make(ProductSales::class)->fullWidth()->withCustomDateRange(),
        ];
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'shop';
    }
}
