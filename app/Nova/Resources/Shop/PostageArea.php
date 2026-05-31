<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Jobs\Shop\SyncShippingToGoogleMerchantJob;
use App\Models\Shop\ShopPostageCountryArea;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<ShopPostageCountryArea> */
/**
 * @codeCoverageIgnore
 */
class PostageArea extends Resource
{
    /** @var class-string<ShopPostageCountryArea> */
    public static string $model = ShopPostageCountryArea::class;

    public static $title = 'area';

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Text::make('Area'),

            Text::make('Delivery Timescale'),
        ];
    }

    public function authorizedToAdd(NovaRequest $request, $model)
    {
        return $request->query('component') === 'belongsto.belongs-to-field.area';
    }

    public function authorizedToUpdate(Request $request)
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
