<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProductVariant;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class ProductVariant extends Resource
{
    public static $model = ShopProductVariant::class;

    public static $searchable = false;

    public static $title = 'title';

    public static $clickAction = 'view';

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->fullWidth()->hide(),

            Text::make('Title')->fullWidth()->help('Leave empty for only one variant')->default(''),

            Textarea::make('Description', 'short_description')
                ->maxlength(255)
                ->nullable()
                ->fullWidth()
                ->alwaysShow(),

            Number::make('Quantity', 'quantity')->fullWidth()->rules(['required']),

            Number::make('Total Sold')->exceptOnForms(),

            Number::make('Weight')->fullWidth()->rules(['required']),

            Boolean::make('Live')->fullWidth(),

            KeyValue::make('Icon')
                ->nullable()
                ->help('Leave blank for most occasions, lets you set an icon to display with the variant select, eg coloured circles on stickers.'),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return $query
            ->with(['product'])
            ->addSelect(['total_sold' => ShopOrderItem::query()
                ->selectRaw('sum(quantity)')
                ->whereColumn('product_variant_id', 'shop_product_variants.id')
                ->whereRelation('order', fn (Builder $relation) => $relation->whereIn('state_id', [
                    OrderState::PAID,
                    OrderState::READY,
                    OrderState::SHIPPED,
                ])),
            ]);
    }

    public static function detailQuery(NovaRequest $request, \Illuminate\Contracts\Database\Eloquent\Builder $query)
    {
        return self::indexQuery($request, $query);
    }

    protected static function fillFields(NovaRequest $request, $model, $fields): array
    {
        $fillFields = parent::fillFields($request, $model, $fields);
        $variant = $fillFields[0];

        if ($variant->title === null) {
            $variant->title = '';
        }

        return $fillFields;
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
