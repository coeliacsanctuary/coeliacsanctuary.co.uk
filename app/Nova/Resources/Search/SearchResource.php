<?php

declare(strict_types=1);

namespace App\Nova\Resources\Search;

use App\Models\Search\Search;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class SearchResource extends Resource
{
    public static $model = Search::class;

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
        return 'Search Terms';
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->hide(),

            Text::make('Term'),

            Number::make('Number Of Searches', 'history_count')->sortable(),

            DateTime::make('First Searched', 'created_at')->sortable(),

            DateTime::make('Most Recent Search', 'updated_at')->sortable(),

            HasOne::make('AI Scoring', 'aiResponse', SearchAiResponseResource::class),

            HasMany::make('Search History', 'history', SearchHistoryResource::class),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->withCount('history')
            ->when($request->missing('orderByDirection'), fn (Builder $builder) => $builder->reorder('updated_at', 'desc'));
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        return self::indexQuery($request, $query);
    }
}
