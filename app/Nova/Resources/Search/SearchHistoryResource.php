<?php

declare(strict_types=1);

namespace App\Nova\Resources\Search;

use App\Models\SearchHistory;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\Geocoder\Facades\Geocoder;

class SearchHistoryResource extends Resource
{
    public static $model = SearchHistory::class;

    public static $perPageViaRelationship = 10;

    public function authorizedToView(Request $request): bool
    {
        return true;
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->hide(),

            Text::make('User Lat/Lng', fn (SearchHistory $model) => $model->lat ? "{$model->lat}, {$model->lng}" : 'Unknown'),

            Text::make('User Geocoded Location', function (SearchHistory $model) {
                if ( ! $model->lat) {
                    return null;
                }

                $geocoder = Geocoder::getAddressForCoordinates($model->lat, $model->lng);

                return Arr::get($geocoder, 'formatted_address');
            }),

            DateTime::make('Created At'),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->when($request->missing('orderByDirection'), fn (Builder $builder) => $builder->reorder('updated_at', 'desc'));
    }
}
