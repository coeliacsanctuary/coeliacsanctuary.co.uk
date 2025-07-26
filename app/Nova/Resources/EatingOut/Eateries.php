<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\Collections\Collection as CollectionModel;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use App\Notifications\EatingOut\EateryRecommendationAddedNotification;
use App\Nova\Actions\EatingOut\GenerateSealiacOverview;
use App\Nova\Resource;
use App\Nova\Resources\EatingOut\PolymorphicPanels\EateryFeaturesPolymorphicPanel;
use App\Nova\Resources\Main\SealiacOverviews;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Jpeters8889\AddressField\AddressField;
use Jpeters8889\EateryLocationSearch\EateryLocationSearch;
use Jpeters8889\EateryOpeningTimes\EateryOpeningTimes;
use Jpeters8889\HiddenWritableField\HiddenWritableField;
use Jpeters8889\PolymorphicPanel\PolymorphicPanel;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/** @extends resource<Eatery> */
/**
 * @codeCoverageIgnore
 */
class Eateries extends Resource
{
    /** @var class-string<Eatery> */
    public static string $model = Eatery::class;

    public static $title = 'name';

    public static $search = ['id', 'name', 'town', 'county'];

    public function authorizedToReplicate(Request $request)
    {
        return true;
    }

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public function fields(NovaRequest $request)
    {
        $detailsFields = [
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
                        $county = EateryCounty::query()->find($request->county);

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
                        $town = EateryTown::query()->find($request->town);

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
                        $county = EateryCounty::query()->where('id', $countyId)->first();

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
                    ->default(fn () => json_encode([
                        'address' => Arr::get(Cache::get('admin-recommend-place'), 'place_location'),
                        'latitude' => null,
                        'longitude' => null,
                    ]))
                    ->latitudeField('lat')
                    ->longitudeField('lng'),
            ]),

            Panel::make('Contact Details', [
                Text::make('Phone Number', 'phone')->fullWidth()->nullable()->rules(['max:50'])->hideFromIndex(),

                URL::make('Website')
                    ->default(Arr::get(Cache::get('admin-recommend-place'), 'place_web_address'))
                    ->fullWidth()
                    ->nullable()
                    ->rules(['max:255'])
                    ->hideFromIndex(),

                URL::make('GF Menu Link')->fullWidth()->nullable()->rules(['max:255'])->hideFromIndex(),
            ]),

            Panel::make('Details', [
                Select::make('Type', 'type_id')
                    ->displayUsingLabels()
                    ->fullWidth()
                    ->filterable()
                    ->rules(['required'])
                    ->default(1)
                    ->options([
                        1 => 'Eatery',
                        2 => 'Attraction',
                        3 => 'Hotel',
                    ]),

                Select::make('Venue Type', 'venue_type_id')
                    ->hideFromIndex()
                    ->default(Arr::get(Cache::get('admin-recommend-place'), 'place_venue_type_id'))
                    ->dependsOn(['type_id'], function (Select $field, NovaRequest $request) {
                        return match ($request->type_id) {
                            default => $field->options($this->getVenueTypes(1)),
                            2 => $field->options($this->getVenueTypes(2)),
                            3 => $field->hide()->setValue(26),
                        };
                    })
                    ->fullWidth()
                    ->rules(['required']),

                Select::make('Cuisine', 'cuisine_id')
                    ->hideFromIndex()
                    ->fullWidth()
                    ->dependsOn(['type_id'], function (Select $field, NovaRequest $request) {
                        return match ($request->type_id) {
                            1 => $field->options($this->getCuisines()),
                            default => $field->hide()->setValue(29),
                        };
                    })
                    ->rules(['required']),

                Textarea::make('Info')
                    ->alwaysShow()
                    ->default(Arr::get(Cache::get('admin-recommend-place'), 'place_info'))
                    ->dependsOn(['type_id'], function (Textarea $field, NovaRequest $request) {
                        return match ($request->type_id) {
                            2 => $field->hide()->nullable()->setValue(null),
                            default => $field->show()->rules(['required']),
                        };
                    })
                    ->fullWidth(),

                EateryOpeningTimes::make('Opening Times', 'openingTimes')
                    ->dependsOn(['type_id'], function (EateryOpeningTimes $field, NovaRequest $request) {
                        if ($request->type_id === 3) {
                            $field->hide();
                        }

                        return $field;
                    }),
            ]),
        ];

        return [
            HiddenWritableField::make('Recommendation ID', 'place_recommendation_id')
                ->default(Arr::get(Cache::get('admin-recommend-place'), 'place_recommendation_id')),

            ID::make('id')->hide(),

            Text::make('Name', 'name')
                ->fullWidth()
                ->rules(['required', 'max:200'])
                ->default(Arr::get(Cache::get('admin-recommend-place'), 'place_name'))
                ->sortable(),

            Text::make('Location', 'full_location')
                ->displayUsing(function ($bar, $branch) {
                    $branch->loadMissing(['town', 'county', 'country']);

                    return $branch->full_location;
                })
                ->fullWidth()
                ->exceptOnForms(),

            Text::make('Reviews', 'reviews_count')->fullWidth()->onlyOnIndex()->sortable()->filterable(),

            ...$request->viaRelationship() === false ? $detailsFields : [],

            Panel::make('Features', [
                PolymorphicPanel::make('Features', new EateryFeaturesPolymorphicPanel())->display('row'),
            ]),

            Boolean::make('Live')->filterable(),

            Boolean::make('Closed Down')
                ->filterable()
                ->help('If a location has closed down, then as long as it is still live then it will be removed from lists and maps, but the page will still load for search engines.'),

            DateTime::make('Created At')->exceptOnForms(),

            DateTime::make('Last Updated', 'updated_at')->exceptOnForms(),

            MorphMany::make('Sealiac Overviews', resource: SealiacOverviews::class),

            HasMany::make('Reviews', resource: Reviews::class),

            HasMany::make('Suggested Edits', resource: SuggestedEdits::class),

            HasMany::make('Reports', resource: PlaceReports::class),
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
            ->where('county_id', '>', 1)
            ->select('*')
            ->selectSub('select country from wheretoeat_countries where wheretoeat_countries.id = wheretoeat.country_id', 'order_country')
            ->selectSub('select county from wheretoeat_counties where wheretoeat_counties.id = wheretoeat.county_id', 'order_county')
            ->selectSub('select town from wheretoeat_towns where wheretoeat_towns.id = wheretoeat.town_id', 'order_town')
            ->with(['country', 'county',
                'town' => fn (Relation $relation) => $relation->withoutGlobalScopes(),
                'type', 'county.country', 'openingTimes',
            ])
            ->withCount(['reviews' => fn (Builder $builder) => $builder->withoutGlobalScopes()])
            ->when($request->missing('orderByDirection'), fn (Builder $builder) => $builder->reorder('order_country')->orderBy('order_county')->orderBy('order_town'));
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

    public function actions(NovaRequest $request): array
    {
        return [
            GenerateSealiacOverview::make()
                ->showInline()
                ->canRun(fn (NovaRequest $request, Eatery $model) => $model->live === true && $model->closed_down === false && $model->reviews_count > 0),
        ];
    }

    /** @param Eatery $model */
    public static function afterCreate(NovaRequest $request, Model $model): void
    {
        if ( ! $request->filled('place_recommendation_id')) {
            return;
        }

        $placeRecommendation = EateryRecommendation::query()->find($request->get('place_recommendation_id'));

        if ( ! $placeRecommendation) {
            return;
        }

        $placeRecommendation->update(['completed' => true]);

        (new AnonymousNotifiable())
            ->route('mail', $placeRecommendation->email)
            ->notify(new EateryRecommendationAddedNotification($placeRecommendation, $model));
    }
}
