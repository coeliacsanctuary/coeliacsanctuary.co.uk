<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\TravelCardSearchTermHistory;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class TravelCardSearchHistory extends Resource
{
    /** @var class-string<TravelCardSearchTermHistory> */
    public static string $model = TravelCardSearchTermHistory::class;

    public static $search = ['term'];

    public function authorizedToView(Request $request): bool
    {
        return false;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Text::make('Term'),

            Number::make('Searches', 'hits')->sortable(),

            DateTime::make('First Search', 'created_at')->sortable(),

            DateTime::make('Most Recent Search', 'updated_at')->sortable(),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return $query->when(
            $request->missing('orderByDirection'),
            fn (Builder $builder) => $builder->reorder('updated_at', 'desc'),
        );
    }
}
