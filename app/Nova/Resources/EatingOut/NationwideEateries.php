<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\Collections\Collection as CollectionModel;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryVenueType;
use App\Nova\Actions\EatingOut\GenerateSealiacOverview;
use App\Nova\Resource;
use App\Nova\Resources\EatingOut\PolymorphicPanels\EateryFeaturesPolymorphicPanel;
use App\Nova\Resources\Main\SealiacOverviews;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Jpeters8889\PolymorphicPanel\PolymorphicPanel;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
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
class NationwideEateries extends Resource
{
    /** @var class-string<Eatery> */
    public static string $model = Eatery::class;

    public static $title = 'name';

    public static $search = ['id', 'name'];

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public static $clickAction = 'view';

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Text::make('Name', 'name')->fullWidth()->rules(['required', 'max:200'])->sortable(),

            Text::make('Branches', 'nationwide_branches_count')->onlyOnIndex(),

            Boolean::make('Live'),

            Panel::make('Contact Details', [
                Text::make('Phone Number', 'phone')->fullWidth()->nullable()->rules(['max:50'])->onlyOnForms(),

                URL::make('Website')->fullWidth()->nullable()->rules(['max:255'])->onlyOnForms(),

                URL::make('GF Menu Link')->fullWidth()->nullable()->rules(['max:255'])->onlyOnForms(),
            ]),

            ...$request->viaRelationship() === false ? [Panel::make('Details', [
                Hidden::make('Type', 'type_id')->default(1),

                Select::make('Venue Type', 'venue_type_id')
                    ->hideFromIndex()
                    ->displayUsingLabels()
                    ->options($this->getVenueTypes(1))
                    ->fullWidth()
                    ->rules(['required']),

                Select::make('Cuisine', 'cuisine_id')
                    ->hideFromIndex()
                    ->displayUsingLabels()
                    ->fullWidth()
                    ->options($this->getCuisines())
                    ->rules(['required']),

                Textarea::make('Info')
                    ->alwaysShow()
                    ->rules(['required'])
                    ->fullWidth(),
            ])] : [],

            Panel::make('Features', [
                PolymorphicPanel::make('Features', new EateryFeaturesPolymorphicPanel())->display('row'),
            ]),

            HasMany::make('Branches', 'nationwideBranches', NationwideBranches::class),

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
            ->where('county_id', 1)
            ->withCount(['nationwideBranches' => fn (Builder $builder) => $builder->withoutGlobalScopes()])
            ->when($request->missing('orderByDirection'), fn (Builder $builder) => $builder->reorder('name'));
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

    protected static function fillFields(NovaRequest $request, $model, $fields): array
    {
        $fillFields = parent::fillFields($request, $model, $fields);
        $eatery = $fillFields[0];

        $eatery->address = '';
        $eatery->lat = 0;
        $eatery->lng = 0;
        $eatery->country_id = 1;
        $eatery->county_id = 1;
        $eatery->town_id = 529;

        return $fillFields;
    }

    public static function usesScout()
    {
        return false;
    }
}
