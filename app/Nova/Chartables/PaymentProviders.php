<?php

declare(strict_types=1);

namespace App\Nova\Chartables;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Jpeters8889\ApexCharts\Chartable;
use Jpeters8889\ApexCharts\DTO\DateRange;

class PaymentProviders extends Chartable
{
    public function type(): string
    {
        return static::AREA_CHART;
    }

    /** @return array<Collection<string, Collection<int, ShopOrder>>> */
    public function getData(Carbon $startDate, Carbon $endDate): array
    {
        return [
            ShopOrder::query()
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->whereIn('state_id', [OrderState::PAID, OrderState::READY, OrderState::SHIPPED])
                ->whereNotNull('order_key')
                ->with(['payment'])
                ->get()
                ->groupBy('payment.payment_type_id'),
        ];
    }

    /** @param Collection<int, ShopOrder> $collection */
    protected function formatResult(Collection $collection): int|float
    {
        return $collection->count();
    }

    protected function data(DateRange $dateRange): array
    {
        $data = $this->calculateData($dateRange);

        $colours = [
            'Card' => '#DBBC25',
            'Link' => '#80CCFC',
            'PayPal' => '#addaf9',
            'Apple Pay' => '#17a417',
            'Google Pay' => '#237cbd',
        ];

        $dataLength = count($data);

        $results = [
            'Card' => array_fill(0, $dataLength, 0),
            'Link' => array_fill(0, $dataLength, 0),
            'PayPal' => array_fill(0, $dataLength, 0),
            'Apple Pay' => array_fill(0, $dataLength, 0),
            'Google Pay' => array_fill(0, $dataLength, 0),
        ];

        foreach ($data as $index => $day) {
            foreach ($day as $collection) {
                $collection->each(function (Collection $orders, string $provider) use (&$results, $index): void {
                    if ($provider === 'stripe') {
                        $provider = 'Card';
                    }

                    if ($provider === 'paypal') {
                        $provider = 'PayPal';
                    }

                    $results[$provider][$index] += $this->formatResult($orders);
                });
            }
        }

        return collect($results)
            ->reject(fn (array $items) => empty(array_filter($items)))
            ->map(fn (array $items, string $provider) => [
                'name' => $provider,
                'data' => $items,
                'color' => $colours[$provider],
            ])
            ->values()
            ->toArray();
    }

    public function defaultDateRange(): string
    {
        return self::DATE_RANGE_PAST_MONTH;
    }

    protected function options(): array
    {
        return [
            'chart' => [
                'stacked' => true,
            ],
            'stroke' => [
                'curve' => 'monotoneCubic',
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'opacityFrom' => 0.6,
                    'opacityTo' => 0.8,
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
        ];
    }
}
