<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Collections\Collection as CollectionModel;
use App\Models\Shop\ShopCategory;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jpeters8889\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<ShopCategory> */
/**
 * @codeCoverageIgnore
 */
class Categories extends Resource
{
    /** @var class-string<ShopCategory> */
    public static string $model = ShopCategory::class;

    public static $title = 'title';

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Text::make('Title')->fullWidth()->rules(['required', 'max:200']),

            Slug::make('Slug')->from('Title')
                ->hideFromIndex()
                ->hideWhenUpdating()
                ->showOnCreating()
                ->fullWidth()
                ->rules(['required', 'max:200', 'unique:shop_categories,slug']),

            Textarea::make('Description')
                ->rows(4)
                ->fullWidth()
                ->alwaysShow()
                ->rules(['required']),

            Text::make('Meta Keywords')
                ->onlyOnForms()
                ->fullWidth()
                ->rules(['required']),

            Textarea::make('Meta Description')
                ->rows(2)
                ->fullWidth()
                ->alwaysShow()
                ->rules(['required']),

            Images::make('Primary Image', 'primary')
                ->addButtonLabel('Select Image')
                ->nullable(),

            Images::make('Social Image', 'social')
                ->addButtonLabel('Select Image')
                ->hideFromIndex()
                ->nullable(),

            Boolean::make('Is Live', fn ($resource) => $resource->live_products > 0)->onlyOnIndex(),

            Number::make('Products', 'products_count')->onlyOnIndex(),

            Number::make('Live Products')->onlyOnIndex(),

            BelongsToMany::make('Products', resource: Products::class),
        ];
    }

    /**
     * @param  Builder<CollectionModel>  $query
     * @return Builder<CollectionModel | Model>
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->withoutGlobalScopes()
            ->withCount([
                'products' => fn (Builder $relation) => $relation->withoutGlobalScopes(),
                'products as live_products',
            ])
            ->reorder(DB::raw('case live_products when 0 then 0 else 1 end'), 'desc')
            ->orderBy('title');
    }

    public function authorizedToView(Request $request)
    {
        return true;
    }
}
