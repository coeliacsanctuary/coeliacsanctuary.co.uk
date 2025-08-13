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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Jpeters8889\AddressField\AddressField;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
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

            Boolean::make('Live'),

            Panel::make('Location', [
                BelongsTo::make('Town', resource: Towns::class)
                    ->onlyOnForms()
                    ->fullWidth()
                    ->showCreateRelationButton(),

                BelongsTo::make('County', resource: Counties::class)
                    ->onlyOnForms()
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
                    ->onlyOnForms()
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
            ->with(['country', 'county',
                'town' => fn (Relation $relation) => $relation->withoutGlobalScopes(),
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

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/' . NationwideEateries::uriKey() . '/' . $resource->resource->wheretoeat_id;
    }

    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return '/resources/' . NationwideEateries::uriKey() . '/' . $resource->resource->wheretoeat_id;
    }
}
