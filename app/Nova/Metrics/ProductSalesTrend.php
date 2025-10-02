<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrderItem;
use DateInterval;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Nova;

/**
 * @codeCoverageIgnore
 */
class ProductSalesTrend extends Trend
{
    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return $this->countByDays(
            $request,
            ShopOrderItem::query()
                ->where('product_id', $request->resourceId)
                ->whereRelation('order', fn (Builder $builder) => $builder->whereIn('state_id', [
                    OrderState::PAID,
                    OrderState::READY,
                    OrderState::SHIPPED,
                ])),
        )
            ->format(['average' => false])
            ->showLatestValue()
            ->showSumValue();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            7 => '7 Days',
            14 => '14 Days',
            30 => Nova::__('30 Days'),
            60 => Nova::__('60 Days'),
            90 => Nova::__('90 Days'),
        ];
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return DateTimeInterface|DateInterval|float|int|null
     */
    public function cacheFor()
    {
        return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'product-sales-trend';
    }
}
