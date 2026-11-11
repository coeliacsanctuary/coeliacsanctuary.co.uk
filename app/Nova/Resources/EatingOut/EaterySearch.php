<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EaterySearchTerm;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class EaterySearch extends Resource
{
    public static $model = EaterySearchTerm::class;

    public static $title = 'term';

    public static $search = ['term'];

    public static $clickAction = 'view';

    public function authorizedToView(Request $request): bool
    {
        return true;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public static function label()
    {
        return 'Eatery Searches';
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->hide(),

            Text::make('Term'),

            Number::make('Search Radius', 'range'),

            Number::make('Number Of Searches', 'searches_count')->sortable(),

            DateTime::make('First Searched', 'created_at')->sortable(),

            DateTime::make('Most Recent Search', 'updated_at')->sortable(),

            HasMany::make('Search History', 'searches', EaterySearchHistory::class),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->withCount('searches')
            ->when($request->missing('orderByDirection'), fn (Builder $builder) => $builder->reorder('updated_at', 'desc'));
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        return self::indexQuery($request, $query);
    }
}
