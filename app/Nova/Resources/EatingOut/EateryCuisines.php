<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EateryCuisine;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<EateryCuisine> */
/**
 * @codeCoverageIgnore
 */
class EateryCuisines extends Resource
{
    /** @var class-string<EateryCuisine> */
    public static string $model = EateryCuisine::class;

    public static $title = 'cuisine';

    public static $search = ['cuisine'];

    public function authorizedToView(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make('id')->hide(),

            Text::make('Name', 'cuisine')->fullWidth()->sortable()->rules(['required', 'max:200']),

            Number::make('Eateries', 'eateries_count')->onlyOnIndex()->sortable(),
        ];
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->withCount(['eateries'])
            ->when($request->missing('orderByDirection'), fn (Builder $builder) => $builder->reorder('cuisine'));
    }
}
