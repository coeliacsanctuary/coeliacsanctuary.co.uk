<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\Collections\Collection as CollectionModel;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use App\Nova\Resource;
use App\Nova\Resources\EatingOut\PolymorphicPanels\EateryFeaturesPolymorphicPanel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Jpeters8889\AddressField\AddressField;
use Jpeters8889\EateryOpeningTimes\EateryOpeningTimes;
use Jpeters8889\PolymorphicPanel\PolymorphicPanel;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/** @extends Resource<Eatery> */
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
                BelongsTo::make('Town', resource: Towns::class)
                    ->searchable()
                    ->hideFromIndex()
                    ->fullWidth()
                    ->showCreateRelationButton(),

                BelongsTo::make('County', resource: Counties::class)
                    ->hideFromIndex()
                    ->fullWidth()
                    ->hide()
                    ->displayUsing(fn ($county) => $county->county)
                    ->dependsOn(['town'], function (BelongsTo $field, NovaRequest $request): BelongsTo {
                        $field->show();

                        /** @var EateryTown $town */
                        $town = EateryTown::query()->find($request->town);

                        if ($town) {
                            $field->setValue($town->county_id);
                        }

                        return $field;
                    }),

                BelongsTo::make('Country', resource: Countries::class)
                    ->hideFromIndex()
                    ->fullWidth()
                    ->hide()
                    ->dependsOn(['county'], function (BelongsTo $field, NovaRequest $request): BelongsTo {
                        $field->show();

                        /** @var EateryCounty | null $county */
                        $county = EateryCounty::query()->find($request->county);

                        if ($county) {
                            $field->setValue($county->country_id);
                        }

                        return $field;
                    }),

                AddressField::make('Address')
                    ->required()
                    ->latitudeField('lat')
                    ->longitudeField('lng'),
            ]),

            Panel::make('Contact Details', [
                Text::make('Phone Number', 'phone')->fullWidth()->nullable()->rules(['max:50'])->hideFromIndex(),

                URL::make('Website')->fullWidth()->nullable()->rules(['max:255'])->hideFromIndex(),

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
            ID::make('id')->hide(),

            Text::make('Name', 'name')
                ->fullWidth()
                ->rules(['required', 'max:200'])
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
}
