<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use App\Nova\Actions\EatingOut\GenerateSealiacOverview;
use App\Nova\Resource;
use App\Nova\Resources\Main\SealiacOverviews;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Jpeters8889\AddressField\AddressField;
use Jpeters8889\EateryLocationSearch\EateryLocationSearch;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/** @extends resource<NationwideBranch> */
/**
 * @codeCoverageIgnore
 */
class NationwideBranches extends Resource
{
    /** @var class-string<NationwideBranch> */
    public static string $model = NationwideBranch::class;

    public static $title = 'name';

    public static $perPageViaRelationship = 25;

    public static $search = ['id', 'name', 'town', 'county'];

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Text::make('Name', 'name')->fullWidth()->rules(['max:200'])->sortable(),

            Text::make('Location', 'full_location')
                ->displayUsing(function ($bar, $branch) {
                    $branch->loadMissing(['town', 'county', 'country']);

                    return $branch->full_location;
                })
                ->fullWidth()
                ->exceptOnForms(),

            Text::make('Address')
                ->fullWidth()
                ->exceptOnForms(),

            Boolean::make('Live'),

            Panel::make('Location', [
                EateryLocationSearch::make('Location Search', 'location', fn () => null)
                    ->fullWidth()
                    ->onlyOnForms(),

                BelongsTo::make('Country', resource: Countries::class)
                    ->hideFromIndex()
                    ->fullWidth()
                    ->hide()
                    ->dependsOn(['location'], function (BelongsTo $field, NovaRequest $request): BelongsTo {
                        if ($request->filled('location')) {
                            $location = $request->json('location');

                            if (Arr::has($location, 'countryId')) {
                                $field->setValue($location['countryId']);
                            }
                        }

                        return $field;
                    })
                    ->dependsOn(['county'], function (BelongsTo $field, NovaRequest $request): BelongsTo {
                        $field->show();

                        /** @var EateryCounty | null $county */
                        $county = EateryCounty::withoutGlobalScopes()->find($request->county);

                        if ($county) {
                            $field->setValue($county->country_id);
                        }

                        return $field;
                    }),

                BelongsTo::make('County', resource: Counties::class)
                    ->searchable()
                    ->dependsOn(['location'], function (BelongsTo $field, NovaRequest $request): BelongsTo {
                        if ($request->filled('location')) {
                            $location = $request->json('location');

                            if (Arr::has($location, 'countyId')) {
                                $field->setValue($location['countyId']);
                            }
                        }

                        return $field;
                    })
                    ->dependsOn('country', function (BelongsTo $field, NovaRequest $request, FormData $data): void {
                        $field->relatableQueryUsing(fn (NovaRequest $subRequest, Builder $query) => $query->where('wheretoeat_counties.country_id', $data->get('country')));
                    })
                    ->dependsOn('town', function (BelongsTo $field, NovaRequest $request): BelongsTo {
                        $field->show();

                        /** @var EateryTown $town */
                        $town = EateryTown::withoutGlobalScopes()->find($request->town);

                        if ($town) {
                            $field->setValue($town->county_id);
                        }

                        return $field;
                    })
                    ->hideFromIndex()
                    ->fullWidth()
                    ->hide()
                    ->displayUsing(fn ($county) => $county->county)
                    ->showCreateRelationButton(),

                BelongsTo::make('Town', resource: Towns::class)
                    ->searchable()
                    ->dependsOn(['location'], function (BelongsTo $field, NovaRequest $request): BelongsTo {
                        if ($request->filled('location')) {
                            $location = $request->json('location');

                            if (Arr::has($location, 'townId')) {
                                $field->setValue($location['townId']);
                            }
                        }

                        return $field;
                    })
                    ->dependsOn('county', function (BelongsTo $field, NovaRequest $request, FormData $data): void {
                        $field->relatableQueryUsing(fn (NovaRequest $subRequest, Builder $query) => $query->where('county_id', $data->get('county')));
                    })
                    ->hideFromIndex()
                    ->fullWidth()
                    ->showCreateRelationButton(),

                BelongsTo::make('Area', resource: Areas::class)
                    ->searchable()
                    ->dependsOn(['location'], function (BelongsTo $field, NovaRequest $request): BelongsTo {
                        if ($request->filled('location')) {
                            $location = $request->json('location');

                            if (Arr::has($location, 'areaId')) {
                                $field->setValue($location['areaId']);
                            }
                        }

                        return $field;
                    })
                    ->dependsOn('town', function (BelongsTo $field, NovaRequest $request, FormData $data): void {
                        $field->relatableQueryUsing(fn (NovaRequest $subRequest, Builder $query) => $query->where('town_id', $data->get('town')));
                    })
                    ->dependsOn(['county'], function (BelongsTo $field, NovaRequest $request): BelongsTo {
                        $countyId = $request->input('county');
                        $county = EateryCounty::withoutGlobalScopes()->where('id', $countyId)->first();

                        if ($county?->slug === 'london') {
                            $field->show();
                        } else {
                            $field->hide();
                        }

                        return $field;
                    })
                    ->hideFromIndex()
                    ->fullWidth()
                    ->showCreateRelationButton()
                    ->hide()
                    ->nullable(),

                AddressField::make('Address')
                    ->required()
                    ->latitudeField('lat')
                    ->longitudeField('lng'),
            ]),

            MorphMany::make('Sealiac Overviews', resource: SealiacOverviews::class),

            HasMany::make('Reviews', resource: Reviews::class),

            HasMany::make('Reports', resource: PlaceReports::class),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->withoutGlobalScopes()
            ->select('*')
            ->selectSub('select country from wheretoeat_countries where wheretoeat_countries.id = wheretoeat_nationwide_branches.country_id', 'order_country')
            ->selectSub('select county from wheretoeat_counties where wheretoeat_counties.id = wheretoeat_nationwide_branches.county_id', 'order_county')
            ->selectSub('select town from wheretoeat_towns where wheretoeat_towns.id = wheretoeat_nationwide_branches.town_id', 'order_town')
            ->selectSub('select area from wheretoeat_areas where wheretoeat_areas.id = wheretoeat_nationwide_branches.area_id', 'order_area')
            ->with(['country', 'county',
                'town' => fn (Relation $relation) => $relation->withoutGlobalScopes(),
                'area' => fn (Relation $relation) => $relation->withoutGlobalScopes(),
            ])
            ->withCount(['reviews' => fn (Builder $builder) => $builder->withoutGlobalScopes()])
            ->when($request->missing('orderByDirection'), fn (Builder $builder) => $builder->reorder('order_country')->orderBy('order_county')->orderBy('order_town')->orderBy('order_area'));
    }

    public static function relatableQuery(NovaRequest $request, EloquentBuilder $query)
    {
        return static::indexQuery($request, $query);
    }

    protected function getVenueTypes($typeId = null): array
    {
        return EateryVenueType::query()
            ->when($typeId, fn (Builder $query) => $query->where('type_id', $typeId))
            ->get()
            ->mapWithKeys(fn (EateryVenueType $venueType) => [$venueType->id => $venueType->venue_type])
            ->toArray();
    }

    protected function getCuisines(): array
    {
        return EateryCuisine::query()
            ->get()
            ->mapWithKeys(fn (EateryCuisine $cuisine) => [$cuisine->id => $cuisine->cuisine])
            ->toArray();
    }

    public static function afterCreate(NovaRequest $request, Model $model): void
    {
        $model->eatery->touch();
    }

    public static function afterUpdate(NovaRequest $request, Model $model): void
    {
        $model->eatery->touch();
    }

    public function actions(NovaRequest $request): array
    {
        return [
            GenerateSealiacOverview::make()
                ->showInline()
                ->canRun(function (NovaRequest $request, NationwideBranch $model) {
                    $model->reviews_count ?: $model->loadCount('reviews');

                    return $model->live === true && $model->reviews_count > 0;
                }),
        ];
    }

    protected static function fillFields(NovaRequest $request, $model, $fields): array
    {
        $fillFields = parent::fillFields($request, $model, $fields);
        $branch = $fillFields[0];

        unset($branch->location);

        return $fillFields;
    }

    public static function usesScout()
    {
        return false;
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/' . NationwideEateries::uriKey() . '/' . $resource->resource->wheretoeat_id;
    }

    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return '/resources/' . NationwideEateries::uriKey() . '/' . $resource->resource->wheretoeat_id;
    }
}
