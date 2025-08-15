<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\Collections\Collection as CollectionModel;
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

/** @extends resource<EateryCounty> */
/**
 * @codeCoverageIgnore
 */
class Towns extends Resource
{
    /** @var class-string<EateryCounty> */
    public static string $model = EateryTown::class;

    public static $title = 'town';

    public static $search = ['town'];

    public static $perPageViaRelationship = 25;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Text::make('Name', 'town')->fullWidth()->rules(['required', 'max:200'])->sortable(),

            Slug::make('Slug')->from('town')
                ->hideFromIndex()
                ->hideWhenUpdating()
                ->showOnCreating()
                ->fullWidth()
                ->rules(['required', 'max:200', 'unique:wheretoeat_towns,slug']),

            BelongsTo::make('County', resource: Counties::class)
                ->filterable()
                ->fullWidth()
                ->displayUsing(function ($county) {
                    if ( ! $county->relationLoaded('country')) {
                        $county->load('country');
                    }

                    return $county->country->country . ' - ' . $county->county;
                }),

            Text::make('Lat / Lng', 'latlng')->fullWidth(),

            Images::make('Image', 'primary')
                ->onlyOnForms()
                ->addButtonLabel('Select Image')
                ->nullable(),

            Boolean::make('Live', fn () => $this->eateries_count > 0)->onlyOnIndex(),

            Number::make('Eateries', 'eateries_count')->onlyOnIndex()->sortable(),

            //            HasMany::make('eateries')
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
            ->with(['county', 'county.country'])
            ->withCount(['eateries' => fn (Builder $relation) => $relation->where('live', true)])
            ->when($request->missing('orderByDirection'), fn (Builder $builder) => $builder->reorder('town'));
    }

    protected static function fillFields(NovaRequest $request, $model, $fields): array
    {
        $fillFields = parent::fillFields($request, $model, $fields);
        $town = $fillFields[0];

        $town->legacy = '';

        return $fillFields;
    }
}
