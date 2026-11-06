<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EateryTrip;
use App\Nova\Metrics\TripsPerDay;
use App\Nova\Metrics\TripsPlanned;
use App\Nova\Metrics\TripsRevisited;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<EateryTrip> */
class EateryTrips extends Resource
{
    public static $model = EateryTrip::class;

    public static $title = 'key';

    public static $search = ['key'];

    public static function label(): string
    {
        return 'Trips';
    }

    public static function singularLabel(): string
    {
        return 'Trip';
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToView(Request $request): bool
    {
        return true;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->hide(),

            Text::make('Trip', fn (EateryTrip $trip): string => collect([Arr::first($trip->points), Arr::last($trip->points)])
                ->map(fn (?array $point): string => explode(',', $point['label'] ?? '')[0])
                ->join(' → ')),

            Text::make('Route', fn (EateryTrip $trip): string => collect($trip->points)
                ->map(fn (array $point): string => $point['label'])
                ->join('<br />'))
                ->asHtml()
                ->onlyOnDetail(),

            Number::make('Via Points', fn (EateryTrip $trip): int => max(count($trip->points) - 2, 0)),

            Text::make('Search Distance', fn (EateryTrip $trip): string => "{$trip->distance} miles"),

            Text::make('Stops', fn (EateryTrip $trip): string => match ($trip->stop_interval) {
                60 => 'Every hour',
                90 => 'Every 1½ hours',
                120 => 'Every 2 hours',
                180 => 'Every 3 hours',
                default => 'None',
            }),

            Text::make('Driving Distance', fn (EateryTrip $trip): string => round($trip->route_distance / 1609.344, 1) . ' miles'),

            Text::make('Driving Time', fn (EateryTrip $trip): string => intdiv((int) $trip->route_duration, 3600) . 'h ' . (int) round(((int) $trip->route_duration % 3600) / 60) . 'm'),

            URL::make('View', fn (EateryTrip $trip): string => route('eating-out.trip-planner.show', ['eateryTrip' => $trip]))
                ->displayUsing(fn (): string => 'Open trip'),

            DateTime::make('Created At')->sortable(),

            DateTime::make('Last Visited At')->sortable(),

            DateTime::make('Updated At')->sortable()->onlyOnDetail(),
        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [
            TripsPlanned::make()->width('1/3'),
            TripsPerDay::make()->width('1/3'),
            TripsRevisited::make()->width('1/3'),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->when($request->missing('orderByDirection'), fn (Builder $builder) => $builder->reorder('created_at', 'desc'));
    }
}
