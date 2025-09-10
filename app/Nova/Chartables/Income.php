<?php

declare(strict_types=1);

namespace App\Nova\Chartables;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPaymentRefund;
use Carbon\Carbon;
use Jpeters8889\ApexCharts\Chartable;
use Jpeters8889\ApexCharts\DTO\DateRange;

class Income extends Chartable
{
    public function type(): string
    {
        return static::AREA_CHART;
    }

    public function getData(Carbon $startDate, Carbon $endDate): array
    {
        $data = [];

        foreach ([false, true] as $isDigitalOnly) {
            $total = 0;

            ShopOrder::query()
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->whereNotNull('order_key')
                ->whereIn('state_id', [OrderState::PAID, OrderState::READY, OrderState::SHIPPED])
                ->where('is_digital_only', $isDigitalOnly)
                ->with('payment')
                ->get()
                ->map(function (ShopOrder $order) use (&$total): void {
                    $total += $order->payment?->total;
                });

            $refunds = ShopPaymentRefund::query()
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->whereRelation('order', fn ($query) => $query->where('is_digital_only', $isDigitalOnly))
                ->sum('amount');

            $data[] = ($total - $refunds) / 100;
        }

        return $data;
    }

    protected function data(DateRange $dateRange): array
    {
        $data = $this->calculateData($dateRange);

        return [
            [
                'name' => 'Physical Orders',
                'data' => array_map(fn ($data) => $data[0], $data),
                'color' => '#80CCFC',
            ],
            [
                'name' => 'Digital Only Orders',
                'data' => array_map(fn ($data) => $data[1], $data),
                'color' => '#DBBC25',
            ],
        ];
    }

    public function defaultDateRange(): string
    {
        return self::DATE_RANGE_PAST_YEAR;
    }

    protected function options(): array
    {
        return [
            'chart' => [
                'stacked' => true,
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'stroke' => [
                'curve' => 'straight',
                'width' => 4,
                'show' => true,
            ],
            'fill' => [
                'type' => 'none',
                'opacity' => 0,
            ],
        ];
    }
}
