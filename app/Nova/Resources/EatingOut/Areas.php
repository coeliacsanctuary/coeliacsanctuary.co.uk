<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\Collections\Collection as CollectionModel;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Jpeters8889\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<EateryArea> */
/**
 * @codeCoverageIgnore
 */
class Areas extends Resource
{
    /** @var class-string<EateryArea> */
    public static string $model = EateryArea::class;

    public static $title = 'area';

    public static $search = ['area'];

    public static $perPageViaRelationship = 25;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Text::make('Name', 'area')->fullWidth()->rules(['required', 'max:200'])->sortable(),

            Slug::make('Slug')->from('Name')
                ->hideFromIndex()
                ->hideWhenUpdating()
                ->showOnCreating()
                ->fullWidth()
                ->rules(['required', 'max:200', 'unique:wheretoeat_areas,slug']),

            Text::make('Lat / Lng', 'latlng')->fullWidth()->rules(['required']),

            BelongsTo::make('Borough', 'town', resource: Towns::class)
                ->filterable()
                ->fullWidth()
                ->displayUsing(function ($town) {
                    return $town->town;
                }),

            Images::make('Image', 'primary')
                ->onlyOnForms()
                ->addButtonLabel('Select Image')
                ->nullable(),

            Boolean::make('Live', fn () => $this->eateries_count > 0)->onlyOnIndex(),

            Number::make('Eateries', 'eateries_count')->onlyOnIndex()->sortable(),
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
            ->with(['town'])
            ->withCount(['eateries' => fn (Builder $relation) => $relation->where('live', true)])
            ->when($request->missing('orderByDirection'), fn (Builder $builder) => $builder->reorder('area'));
    }
}
