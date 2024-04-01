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
use Laravel\Nova\Http\Requests\NovaRequest;

class ProductVariant extends Resource
{
    public static $model = ShopProductVariant::class;

    public static $searchable = false;

    public static $title = 'title';

    public function fields(Request $request): array
    {
        return [
            ID::make()->fullWidth()->hide(),

            Text::make('Title')->nullable()->fullWidth()->help('Leave empty for only one variant'),

            Number::make('In Stock', 'quantity')->fullWidth()->rules(['required']),

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
}
