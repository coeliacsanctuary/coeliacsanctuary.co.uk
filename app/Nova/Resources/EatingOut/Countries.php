<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\Collections\Collection as CollectionModel;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<EateryCounty> */
/**
 * @codeCoverageIgnore
 */
class Countries extends Resource
{
    /** @var class-string<EateryCounty> */
    public static string $model = EateryCountry::class;

    public static $title = 'country';

    public static $search = ['country'];

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Text::make('Name', 'country')->fullWidth()->rules(['required', 'max:200'])->sortable(),

            Number::make('Eateries', 'eateries_count')->onlyOnIndex()->sortable(),

            HasMany::make('Counties', resource: Counties::class),
        ];
    }

    /**
     * @param  Builder<CollectionModel>  $query
     * @return Builder<CollectionModel | Model>
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->withCount(['eateries' => fn (Builder $relation) => $relation->where('live', true)])
            ->when($request->missing('orderByDirection'), fn (Builder $builder) => $builder->reorder('country'));
        /** @phpstan-ignore-line */
    }
}
