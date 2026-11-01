<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EaterySearch;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;

class EaterySearchHistory extends Resource
{
    public static $model = EaterySearch::class;

    public static $perPageViaRelationship = 10;

    public function authorizedToView(Request $request): bool
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
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

            DateTime::make('Created At'),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->when($request->missing('orderByDirection'), fn (Builder $builder) => $builder->reorder('updated_at', 'desc'));
    }
}
