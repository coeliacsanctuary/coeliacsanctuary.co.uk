<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Nova\Actions\Shop\CreateTravelCardFullSet;
use App\Nova\FieldOverrides\Stack;
use App\Nova\Filters\ProductQuantity;
use App\Nova\Filters\ShopLiveProducts;
use App\Nova\Metrics\ProductSalesTrend;
use App\Nova\Resource;
use App\Nova\Resources\Main\SealiacOverviews;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Jpeters8889\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/** @extends resource<ShopProduct> */
/**
 * @codeCoverageIgnore
 */
class Products extends Resource
{
    /** @var class-string<ShopProduct> */
    public static string $model = ShopProduct::class;

    public static $title = 'title';

    public static $clickAction = 'view';

    public static $perPageViaRelationship = 20;

    public static $search = ['title'];

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            new Panel('Introduction', [
                Text::make('Title')
                    ->displayUsing(fn (string $value) => Str::limit($value, 50))
                    ->fullWidth()
                    ->rules(['required', 'max:200']),

                Slug::make('Slug')
                    ->from('Title')
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
                    ->onlyOnIndex(),

                Stack::make('Variants', fn (ShopProduct $resource) => $resource->variants->pluck('title'))->onlyOnIndex(),

                Stack::make('Quantity', fn (ShopProduct $resource) => $resource->variants->pluck('quantity'))
                    ->onlyOnIndex()
                    ->withClasses(fn ($quantity) => $quantity < 10 ? 'font-semibold text-red-500' : ''),

                Stack::make(
                    '',
                    fn (ShopProduct $resource) => $resource
                        ->variants
                        ->pluck('live')
                        ->map(fn (int $live) => $live === 1 ? 'Live' : 'Not Live'),
                )
                    ->onlyOnIndex()
                    ->withClasses(fn ($value) => $value === 'Live' ? 'font-semibold text-green-500' : 'font-semibold text-red-500'),

                Boolean::make('Pinned')->onlyOnForms()->help('Pin the product to top of category page'),

                Text::make('Variant Title')
                    ->fullWidth()
                    ->nullable()
                    ->hideFromIndex()
                    ->help('The label displayed above the variant select, eg, size, colour etc')
                    ->default('Option')
                    ->suggestions(['Size', 'Colour']),

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

            new Panel('Sales', [
                Number::make('Quantity Available', fn (ShopProduct $product) => $product->variants->sum('quantity'))
                    ->hideFromIndex()
                    ->showOnDetail(),

                Number::make('Total Sold', 'total_sold')
                    ->sortable()
                    ->exceptOnForms()
                    ->showOnDetail(),

                Number::make('Sold in last month', 'sold_last_month')
                    ->sortable()
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
                Images::make('Primary Image', 'primary')
                    ->addButtonLabel('Select Image')
                    ->hideFromIndex()
                    ->nullable(),

                Images::make('Social Image', 'social')
                    ->addButtonLabel('Select Image')
                    ->hideFromIndex()
                    ->nullable(),

                Images::make('Additional Images', 'additional')
                    ->addButtonLabel('Select Image(s)')
                    ->hideFromIndex()
                    ->nullable(),
            ]),

            new Panel('Initial Variant', [
                Text::make('Title', 'variants.title')
                    ->fullWidth()
                    ->help('Leave empty for only one variant')
                    ->default('')
                    ->deferrable()
                    ->hideWhenUpdating()
                    ->hideFromIndex()
                    ->hideFromDetail(),

                Number::make('Quantity', 'variants.quantity')
                    ->fullWidth()
                    ->required()
                    ->deferrable()
                    ->hideWhenUpdating()
                    ->hideFromIndex()
                    ->hideFromDetail(),

                Number::make('Weight', 'variants.weight')
                    ->fullWidth()
                    ->required()
                    ->deferrable()
                    ->hideWhenUpdating()
                    ->hideFromIndex()
                    ->hideFromDetail(),

                Boolean::make('Live', 'variants.live')
                    ->fullWidth()
                    ->deferrable()
                    ->hideWhenUpdating()
                    ->hideFromIndex()
                    ->hideFromDetail(),

                Currency::make('Price', 'prices.price')
                    ->asMinorUnits()
                    ->required()
                    ->fullWidth()
                    ->deferrable()
                    ->hideWhenUpdating()
                    ->hideFromIndex()
                    ->hideFromDetail(),
            ]),

            HasMany::make('Variants', resource: ProductVariant::class),

            MorphMany::make('Prices', resource: Price::class),

            HasOne::make('Digital Download Add Ons', 'addOns', resource: ProductAddOn::class),

            BelongsToMany::make('Categories', resource: Categories::class),

            MorphMany::make('Sealiac Overviews', resource: SealiacOverviews::class),

            HasMany::make('Reviews', resource: OrderReviewItem::class),

            BelongsToMany::make('Search Terms', 'travelCardSearchTerms', resource: TravelCardSearchTerms::class)->fields(fn () => [
                Boolean::make('Show on Product Page Country List', 'card_show_on_product_page'),

                Select::make('Language', 'card_language')
                    ->onlyOnForms()
                    ->dependsOn(['card_show_on_product_page'], function (Select $field, NovaRequest $request, FormData $formData): void {
                        $field->hide();

                        /** @phpstan-ignore-next-line */
                        if ($formData->card_show_on_product_page === true) {
                            /** @var Pivot $model */
                            $model = $field->resource;

                            /** @var ShopProduct $product */
                            $product = ShopProduct::query()->find($model->product_id);

                            $field
                                ->show()
                                ->options(
                                    Str::of($product->title)
                                        ->before(' Coeliac')
                                        ->explode(' and ')
                                        ->map(fn (string $language) => mb_trim($language))
                                        ->mapWithKeys(fn (string $language) => [Str::headline($language) => Str::headline($language)])
                                        ->put('both', 'Both Languages')
                                        ->toArray()
                                )
                                ->displayUsingLabels();
                        }
                    }),

                Number::make('Priority', 'card_score')
                    ->onlyOnForms()
                    ->min(0)
                    ->max(100)
                    ->help('Used to order the countries on the product page, highest first.')
                    ->dependsOn(['card_show_on_product_page'], function (Number $field, NovaRequest $request, FormData $formData): void {
                        $field->hide();

                        /** @phpstan-ignore-next-line */
                        if ($formData->card_show_on_product_page === true) {
                            $field->show();
                        }
                    }),
            ]),
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
     * @param  Builder<ShopProduct>  $query
     * @return Builder<ShopProduct | Model>
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
            ->addSelect(['sold_last_month' => ShopOrderItem::query()
                ->selectRaw('sum(quantity)')
                ->whereColumn('product_id', 'shop_products.id')
                ->whereRelation(
                    'order',
                    fn (Builder $relation) => $relation
                        ->whereIn('state_id', [
                            OrderState::PAID,
                            OrderState::READY,
                            OrderState::SHIPPED,
                        ])
                        ->where('created_at', '>', now()->subMonth())
                ),
            ])
            ->withCount('variants')
            ->when(
                $request->missing('orderByDirection'),
                fn (Builder $builder) => $builder->reorder('pinned', 'desc')->orderBy('title'),
            );
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

    public function actions(NovaRequest $request)
    {
        return [
            CreateTravelCardFullSet::make()->standalone(),
        ];
    }

    public static function usesScout()
    {
        return false;
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/' . self::uriKey() . '/' . $resource->getKey();
    }

    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return '/resources/' . self::uriKey() . '/' . $resource->getKey();
    }
}
