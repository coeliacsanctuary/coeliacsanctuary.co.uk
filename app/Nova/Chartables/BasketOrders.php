<?php

declare(strict_types=1);

namespace App\Nova\Chartables;

use App\Models\Shop\ShopOrder;
use Carbon\Carbon;
use Jpeters8889\ApexCharts\Chartable;
use Jpeters8889\ApexCharts\DTO\DateRange;

class BasketOrders extends Chartable
{
    public function type(): string
    {
        return static::LINE_CHART;
    }

    public function name(): string
    {
        return 'Baskets vs Orders';
    }

    public function getData(Carbon $startDate, Carbon $endDate): array
    {
        $orders = ShopOrder::query()
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->whereNotNull('order_key');

        return [
            ShopOrder::query()
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->count(),

            $orders->count(),

            $orders->clone()
                ->where('is_digital_only', false)
                ->where('sent_abandoned_basket_email', false)
                ->count(),

            $orders->clone()
                ->where('is_digital_only', true)
                ->where('sent_abandoned_basket_email', false)
                ->count(),

            $orders->clone()->where('sent_abandoned_basket_email', true)->count(),
        ];
    }

    public function defaultDateRange(): string
    {
        return self::DATE_RANGE_PAST_2_WEEKS;
    }

    protected function data(DateRange $dateRange): array
    {
        $data = $this->calculateData($dateRange);

        $baskets = array_map(fn (array $a) => $a[0], $data);
        $allOrders = array_map(fn (array $a) => $a[1], $data);
        $orders = array_map(fn (array $a) => $a[2], $data);
        $digitalOnlyOrders = array_map(fn (array $a) => $a[3], $data);
        $ordersFromAbandonedBaskets = array_map(fn (array $a) => $a[4], $data);

        return [
            [
                'name' => 'Baskets',
                'data' => $baskets,
                'color' => '#DBBC25',
            ],
            [
                'name' => 'All Orders',
                'data' => $allOrders,
                'color' => '#80CCFC',
            ],
            [
                'name' => 'Physical Orders',
                'data' => $orders,
                'color' => '#addaf9',
            ],
            [
                'name' => 'Digital Orders',
                'data' => $digitalOnlyOrders,
                'color' => '#237cbd',
            ],
            [
                'name' => 'Orders from abandoned baskets',
                'data' => $ordersFromAbandonedBaskets,
                'color' => '#787878',
            ],
        ];
    }
}
