<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\Collections\Collection as CollectionModel;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Jpeters8889\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends resource<EateryCounty> */
/**
 * @codeCoverageIgnore
 */
class Counties extends Resource
{
    /** @var class-string<EateryCounty> */
    public static string $model = EateryCounty::class;

    public static $title = 'county';

    public static $search = ['county'];

    public function fields(NovaRequest $request)
    {
        $countries = EateryCountry::query()
            ->get()
            ->mapWithKeys(fn (EateryCountry $country) => [$country->id => $country->country]);

        return [
            ID::make('id')->hide(),

            Text::make('Name', 'county')->fullWidth()->rules(['required', 'max:200'])->sortable(),

            Slug::make('Slug')->from('county')
                ->hideFromIndex()
                ->hideWhenUpdating()
                ->showOnCreating()
                ->fullWidth()
                ->rules(['required', 'max:200', 'unique:wheretoeat_counties,slug']),

            Select::make('Country', 'country_id')
                ->displayUsingLabels()
                ->filterable()
                ->fullWidth()
                ->options($countries),

            Text::make('Lat / Lng', 'latlng')->fullWidth(),

            Images::make('Image', 'primary')
                ->addButtonLabel('Select Image')
                ->nullable(),

            Boolean::make('Live', fn () => $this->eateries_count > 0)->onlyOnIndex(),

            Number::make('Towns', 'towns_count')->onlyOnIndex()->sortable(),

            Number::make('Eateries', 'eateries_count')->onlyOnIndex()->sortable(),

            HasMany::make('Towns', resource: Towns::class),
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
            ->withCount(['towns', 'eateries' => fn (Builder $relation) => $relation->where('live', true)])
            ->when($request->missing('orderByDirection'), fn (Builder $builder) => $builder->reorder('county'));
    }

    public function title()
    {
        $title = $this->county;

        if ($this->relationLoaded('country')) {
            $title = $this->country->country . ' - ' . $title;
        }

        return $title;
    }

    public function authorizedToView(Request $request)
    {
        return true;
    }

    protected static function fillFields(NovaRequest $request, $model, $fields): array
    {
        $fillFields = parent::fillFields($request, $model, $fields);
        $county = $fillFields[0];

        $county->legacy = '';

        return $fillFields;
    }
}
