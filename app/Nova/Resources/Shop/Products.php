<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Collections\Collection as CollectionModel;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Nova\FieldOverrides\Stack;
use App\Nova\Filters\ProductQuantity;
use App\Nova\Filters\ShopLiveProducts;
use App\Nova\Metrics\ProductSalesTrend;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Jpeters8889\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/** @extends Resource<ShopProduct> */
class Products extends Resource
{
    /** @var class-string<ShopProduct> */
    public static string $model = ShopProduct::class;

    public static $title = 'title';

    public static $clickAction = 'view';

    public static $perPageViaRelationship = 20;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            new Panel('Introduction', [
                Text::make('Title')
                    ->displayUsing(fn (string $value) => Str::limit($value, 50))
                    ->fullWidth()
                    ->rules(['required', 'max:200']),

                Slug::make('Slug')->from('Title')
                    ->hideWhenUpdating()
                    ->hideFromIndex()
                    ->showOnCreating()
                    ->fullWidth()
                    ->rules(['required', 'max:200', 'unique:shop_categories,slug']),

                BelongsTo::make('Shipping Method')->fullWidth()->hideFromIndex(),

                Text::make('Categories', fn (ShopProduct $resource) => $resource->categories->pluck('title')->join(', '))
                    ->fullWidth()
                    ->onlyOnIndex(),

                Currency::make('Price', 'current_price')
                    ->asMinorUnits()
                    ->fullWidth()
                    ->onlyOnIndex()
                    ->showOnCreating()
                    ->deferrable()
                    ->help('In pounds, eg £2.50')
                    ->fillUsing(function (NovaRequest $request, ShopProduct $model, $attribute): void {
                        $model->prices()->create([
                            'price' => $request->input($attribute),
                        ]);
                    }),

                Stack::make('Variants', [
                    fn (ShopProduct $resource) => $resource
                        ->variants
                        ->pluck('title'),
                ])->onlyOnIndex(),

                Stack::make('', [
                    fn (ShopProduct $resource) => $resource
                        ->variants
                        ->pluck('quantity'),
                ])
                    ->onlyOnIndex()
                    ->withClasses(fn ($quantity) => $quantity < 10 ? 'font-semibold text-red-500' : ''),

                Stack::make('', [
                    fn (ShopProduct $resource) => $resource
                        ->variants
                        ->pluck('live')
                        ->map(fn (int $live) => $live === 1 ? 'Live' : 'Not Live'),
                ])
                    ->onlyOnIndex()
                    ->withClasses(fn ($value) => $value === 'Live' ? 'font-semibold text-green-500' : 'font-semibold text-red-500'),

                Boolean::make('Pinned')->onlyOnForms()->help('Pin the product to top of category page'),

                Text::make('Variant Title')->fullWidth()->nullable()->help('The label displayed above the variant select, eg, size, colour etc')->suggestions(['Size', 'Colour']),
            ]),

            new Panel('Sales', [
                Number::make('Quantity Available', fn (ShopProduct $product) => $product->variants->sum('quantity'))
                    ->hideFromIndex()
                    ->showOnDetail(),

                Number::make('Total Sold', 'total_sold')
                    ->exceptOnForms()
                    ->showOnDetail(),
            ]),

            new Panel('Metas', [
                Text::make('Meta Keywords')
                    ->hideFromIndex()
                    ->fullWidth()
                    ->rules(['required']),

                Textarea::make('Meta Description')
                    ->rows(2)
                    ->fullWidth()
                    ->alwaysShow()
                    ->rules(['required']),
            ]),

            new Panel('Image', [
                Images::make('Image', 'primary')
                    ->addButtonLabel('Select Image')
                    ->hideFromIndex()
                    ->nullable(),
            ]),

            new Panel('Details', [
                Textarea::make('Description')
                    ->rows(3)
                    ->fullWidth()
                    ->alwaysShow()
                    ->rules(['required']),

                Textarea::make('Long Description')
                    ->alwaysShow()
                    ->fullWidth()
                    ->rows(8)
                    ->rules(['required']),
            ]),

            HasMany::make('Prices', resource: ProductPrice::class),

            HasMany::make('Variants', resource: ProductVariant::class),
        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [
            ProductSalesTrend::make()->onlyOnDetail()->width('full')->fixedHeight()->height('400px'),
        ];
    }

    public static function detailQuery(NovaRequest $request, $query): Builder
    {
        return static::indexQuery($request, $query);
    }

    /**
     * @param  Builder<CollectionModel>  $query
     * @return Builder<CollectionModel | Model>
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->withoutGlobalScopes()
            ->with([
                'prices',
                'categories' => fn (Relation $builder) => $builder->withoutGlobalScopes(),
                'variants' => fn (Relation $builder) => $builder->withoutGlobalScopes(),
            ])
            ->addSelect(['total_sold' => ShopOrderItem::query()
                ->selectRaw('sum(quantity)')
                ->whereColumn('product_id', 'shop_products.id')
                ->whereRelation('order', fn (Builder $relation) => $relation->whereIn('state_id', [
                    OrderState::PAID,
                    OrderState::READY,
                    OrderState::SHIPPED,
                ])),
            ])
            ->withCount('variants')
            ->reorder('pinned', 'desc')->orderBy('title');
    }

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public function filters(NovaRequest $request): array
    {
        return [
            new ShopLiveProducts(),
            new ProductQuantity(),
        ];
    }
}
