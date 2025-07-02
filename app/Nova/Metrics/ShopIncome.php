<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopPayment;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Nova;

/**
 * @codeCoverageIgnore
 */
class ShopIncome extends Trend
{
    public function calculate(NovaRequest $request)
    {
        return $this
            ->sumByDays(
                $request,
                ShopPayment::query()
                    ->whereHas('order', fn (Builder $builder) => $builder->whereIn('state_id', [
                        OrderState::PAID,
                        OrderState::READY,
                        OrderState::SHIPPED,
                    ])),
                'total',
            )
            ->prefix('£')
            ->transform(fn ($value) => $value / 100)
            ->showSumValue();
    }

    public function ranges()
    {
        return [
            7 => Nova::__('7 Days'),
            14 => Nova::__('14 Days'),
            'MTD' => 'Month To Date',
            30 => Nova::__('30 Days'),
            60 => Nova::__('60 Days'),
            90 => Nova::__('90 Days'),
        ];
    }

    public function cacheFor()
    {
        return now()->addMinutes(5);
    }

    public function uriKey()
    {
        return 'shop-income';
    }
}
