<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\ShopPrice;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class Price extends Resource
{
    public static $model = ShopPrice::class;

    public static $searchable = false;

    public function fields(Request $request): array
    {
        return [
            ID::make()->fullWidth()->hide(),

            Currency::make('Price')
                ->asMinorUnits()
                ->fullWidth(),

            Boolean::make('Current Price', fn (ShopPrice $price) => now()->between($price->start_at, $price->end_at ?? now())),

            Boolean::make('Is a Sale Price', 'sale_price')->fullWidth(),

            Date::make('Start At')->fullWidth()->default(fn () => now()),

            Date::make('End At')->fullWidth()->nullable(),
        ];
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/' . Products::uriKey() . '/' . $resource->resource->product_id;
    }

    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return '/resources/' . Products::uriKey() . '/' . $resource->resource->product_id;
    }
}
