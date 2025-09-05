<?php

declare(strict_types=1);

namespace App\Nova\Chartables;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPaymentRefund;
use Carbon\Carbon;
use Jpeters8889\ApexCharts\Chartable;

class Income extends Chartable
{
    public function type(): string
    {
        return static::LINE_CHART;
    }

    public function getData(Carbon $startDate, Carbon $endDate): int|float
    {
        $total = 0;

        ShopOrder::query()
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->whereNotNull('order_key')
            ->whereIn('state_id', [OrderState::PAID, OrderState::READY, OrderState::SHIPPED])
            ->with('payment')
            ->get()
            ->map(function (ShopOrder $order) use (&$total): void {
                $total += $order->payment?->total;
            });

        $refunds = ShopPaymentRefund::query()
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->sum('amount');

        return ($total - $refunds) / 100;
    }

    public function defaultDateRange(): string
    {
        return self::DATE_RANGE_PAST_YEAR;
    }
}
