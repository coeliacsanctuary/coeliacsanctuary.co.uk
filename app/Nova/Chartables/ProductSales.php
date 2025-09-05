<?php

declare(strict_types=1);

namespace App\Nova\Chartables;

use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Jpeters8889\ApexCharts\Chartable;
use Jpeters8889\ApexCharts\DTO\DateRange;

class ProductSales extends Chartable
{
    protected array $colours = [
        1 => '#80CCFC', // cards
        2 => '#addaf9', // multi
        3 => '#f26522', // cals
        4 => '#ff0000', // wristbands
        5 => '#00ff00', // stickers
        6 => '#FFB6C1', // pens
        7 => '#f5f5f5', // bags
        8 => '#ecd14a', // coeliac other cards
        10 => '#186ba3', // keyrings
        11 => '#ecd14a', // coeliac plus
        12 => '#cccccc', // lanyard
    ];

    protected array $backgroundColours = [];

    protected function products(): Collection
    {
        return once(fn () => ShopProduct::withoutGlobalScopes()
            ->with(['categories' => fn (Relation $builder) => $builder->withoutGlobalScopes()])
            ->orderBy('title')
            ->get()
            ->map(fn (ShopProduct $product) => ['id' => $product->id, 'title' => $product->title, 'category_id' => $product->categories[0]->id ?? null]));
    }

    protected function getLabels(DateRange $dateRange): array
    {
        $labels = [];

        $start = $dateRange->startDate->clone()->startOf($dateRange->unit);
        $end = $dateRange->endDate->clone()->endOf($dateRange->unit);

        $this->products()->each(function (array $product) use (&$labels, $start, $end): void {
            $count = ShopOrderItem::query()
                ->where('product_id', $product['id'])
                ->whereHas('order', fn (Builder $query) => $query->whereHas('payment'))
                ->where('created_at', '>=', $start)
                ->where('created_at', '<=', $end)
                ->count();

            if ($count) {
                $labels[] = $product['title'];
            }
        });

        return $labels;
    }

    protected function calculateData(DateRange $dateRange): array
    {
        $start = $dateRange->startDate->clone()->startOf($dateRange->unit);
        $end = $dateRange->endDate->clone()->endOf($dateRange->unit);

        return $this->getData($start, $end);
    }

    public function getData(Carbon $startDate, Carbon $endDate): array
    {
        $products = [];

        $this->products()->each(function (array $product) use (&$products, $startDate, $endDate): void {
            $item = ShopOrderItem::query()
                ->where('product_id', $product['id'])
                ->whereHas('order', fn (Builder $query) => $query->whereHas('payment'))
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->get(['quantity']);

            if ($item->count() > 0) {
                $count = $item->map(fn (ShopOrderItem $item) => $item->quantity)->toArray();

                $products[] = array_sum($count);
                $this->backgroundColours[] = data_get($this->colours, $product['category_id'], '#cccccc');
            }
        });

        return $products;
    }

    protected function options(): array
    {
        return [
            'colors' => $this->backgroundColours,
            'plotOptions' => [
                'bar' => [
                    'distributed' => true,
                    'horizontal' => true,
                ],
            ],
            'legend' => [
                'show' => false,
            ],
            'chart' => [
                'redrawOnParentResize' => false,
                'redrawOnWindowResize' => false,
            ],
        ];
    }

    protected function data(DateRange $dateRange): array
    {
        return [[
            'name' => $this->name(),
            'data' => $this->calculateData($dateRange),
        ]];
    }

    public function defaultDateRange(): string
    {
        return self::DATE_RANGE_PAST_MONTH;
    }
}
