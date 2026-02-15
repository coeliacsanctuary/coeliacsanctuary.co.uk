<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\ShopProductAddOn;
use App\Nova\Resource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Jpeters8889\AdvancedNovaMediaLibrary\Fields\Files;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class ProductAddOn extends Resource
{
    public static $model = ShopProductAddOn::class;

    public static $searchable = false;

    public static $title = 'name';

    public static $clickAction = 'view';

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->fullWidth()->hide(),

            ...$request->query('viaResource') === 'orders'
                ? [BelongsTo::make('Product', resource: Products::class)->readonly()]
                : [],

            Text::make('Name')->fullWidth(),

            Textarea::make('Description')
                ->maxlength(255)
                ->nullable()
                ->fullWidth()
                ->alwaysShow(),

            Files::make('Digital Download File', 'download')
                ->addButtonLabel('Select File')
                ->setAllowedFileTypes(['application/pdf'])
                ->customHeaders([
                    'ACL' => null,
                ]),

            Currency::make('Extra Fee', 'prices.price')
                ->asMinorUnits()
                ->required()
                ->fullWidth()
                ->deferrable()
                ->hideWhenUpdating()
                ->hideFromIndex()
                ->hideFromDetail(),

            Currency::make('Price', 'current_price')
                ->asMinorUnits()
                ->fullWidth()
                ->exceptOnForms(),

            MorphMany::make('Prices', resource: Price::class),
        ];
    }

    public static function indexQuery(NovaRequest $request, Builder $query): Builder
    {
        return $query->with(['prices']);
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
