<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\ShopSource;
use App\Nova\Resource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class OrderSourcesResource extends Resource
{
    public static $model = ShopSource::class;

    public static $title = 'source';

    public static $search = [
        'id', 'source',
    ];

    public static $clickAction = 'view';

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public function fields(Request $request): array
    {
        return [
            ID::make(),

            Text::make('Source')->rules('required'),

            Number::make('Orders', 'orders_count')->sortable()->readonly(),

            HasMany::make('Orders', resource: Orders::class),
        ];
    }

    public static function indexQuery(NovaRequest $request, Builder $query)
    {
        return $query->reorder('id')->withCount('orders');
    }

    public static function detailQuery(NovaRequest $request, Builder $query)
    {
        return $query->withCount('orders');
    }

    public static function label()
    {
        return 'Order Sources';
    }
}
