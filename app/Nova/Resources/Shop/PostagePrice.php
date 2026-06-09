<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Jobs\Shop\SyncShippingToGoogleMerchantJob;
use App\Models\Shop\ShopPostagePrice;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<ShopPostagePrice> */
/**
 * @codeCoverageIgnore
 */
class PostagePrice extends Resource
{
    /** @var class-string<ShopPostagePrice> */
    public static string $model = ShopPostagePrice::class;

    protected $perPage = 50;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Number::make('Max Weight')
                ->min(0)
                ->rules(['required']),

            Currency::make('Price')
                ->asMinorUnits()
                ->rules(['required']),

            BelongsTo::make('Postage Area', 'area', PostageArea::class)->filterable()->readonly(),

            BelongsTo::make('Shipping Method', 'shippingMethod', ShippingMethod::class)->filterable()->readonly(),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return $query->when(
            $request->missing('orderByDirection'),
            fn (Builder $builder) => $builder->reorder('postage_country_area_id')->orderBy('max_weight'),
        );
    }

    public function authorizedToAdd(NovaRequest $request, $model): bool
    {
        return false;
    }

    public static function afterCreate(NovaRequest $request, Model $model): void
    {
        if ( ! config('google-merchant.enabled')) {
            return;
        }

        SyncShippingToGoogleMerchantJob::dispatch();
    }

    public static function afterUpdate(NovaRequest $request, Model $model): void
    {
        if ( ! config('google-merchant.enabled')) {
            return;
        }

        SyncShippingToGoogleMerchantJob::dispatch();
    }
}
