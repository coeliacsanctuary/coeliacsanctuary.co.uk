<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\TravelCardSearchTerm;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class TravelCardSearchTerms extends Resource
{
    /** @var class-string<TravelCardSearchTerm> */
    public static string $model = TravelCardSearchTerm::class;

    public static $search = ['term'];

    public static $title = 'term';

    public static $clickAction = 'view';

    public static $perPageViaRelationship = 20;

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Text::make('Term')->rules(['required'])->sortable(),

            Select::make('Type')->options(['country' => 'Country', 'language' => 'Language'])->displayUsingLabels()->rules(['required']),

            Number::make('Clicks', 'hits')->exceptOnForms()->sortable(),

            Number::make('Products Count')->exceptOnForms()->sortable(),

            DateTime::make('Most Recent Click', 'updated_at')->sortable()->exceptOnForms(),

            BelongsToMany::make('Products', resource: Products::class)
                ->display(function (Products $product) {
                    /** @var ShopCategory $category */
                    $category = $product->categories()->first();

                    return "{$category->title} - {$product->title}";
                }),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return $query
            ->withCount('products')
            ->when(
                $request->missing('orderByDirection'),
                fn (Builder $builder) => $builder->reorder('updated_at', 'desc'),
            );
    }

    /** @param Builder<ShopProduct> $builder */
    public static function relatableShopProducts(NovaRequest $request, Builder $builder)
    {
        return $builder
            ->whereHas('categories', fn (Builder $builder) => $builder->whereIn('slug', ['standard-coeliac-travel-cards', 'coeliac-cards']))
            ->reorder(DB::raw('(select category_id from shop_product_categories where product_id = shop_products.id) asc, title'));
    }
}
