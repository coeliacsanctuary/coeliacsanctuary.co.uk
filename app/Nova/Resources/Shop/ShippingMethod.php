<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Jobs\Shop\SyncShippingToGoogleMerchantJob;
use App\Models\Shop\ShopShippingMethod;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class ShippingMethod extends Resource
{
    public static $model = ShopShippingMethod::class;

    public static $title = 'shipping_method';

    public function fields(Request $request): array
    {
        return [
            ID::make(),

            Text::make('Shipping Method'),

            HasMany::make('Products', 'products', Products::class),
        ];
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
