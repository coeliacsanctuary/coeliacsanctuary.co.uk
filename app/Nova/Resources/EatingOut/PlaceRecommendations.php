<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryVenueType;
use App\Nova\Actions\EatingOut\CompleteReportOrRecommendation;
use App\Nova\Actions\EatingOut\ConvertRecommendationToEatery;
use App\Nova\Actions\EatingOut\IgnoreReportOrRecommendation;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/**
 * @codeCoverageIgnore
 */
class PlaceRecommendations extends Resource
{
    public static $model = EateryRecommendation::class;

    public static $clickAction = 'preview';

    public static $search = ['place_name', 'place_location', 'place_details'];

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return true;
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->hide(),

            Boolean::make('Completed')->filterable()->hideWhenCreating()->hideWhenUpdating()->showOnPreview(),

            Boolean::make('Ignored')
                ->filterable()
                ->showOnPreview(),

            Panel::make('User', [
                Text::make('name')->showOnPreview()->hideFromIndex(),
                Email::make('email')->showOnPreview()->hideFromIndex(),
            ]),

            Panel::make('Eatery', [
                Text::make('Name', 'place_name')->showOnPreview(),
                Text::make('Location', 'place_location')->displayUsing(fn ($address) => str_replace(',', '<br />', $address))->showOnPreview()->asHtml(),
                URL::make('URL', 'place_web_address')->showOnPreview()->hideFromIndex(),
                Select::make('Venue Type', 'place_venue_type_id')->displayUsingLabels()->options($this->getVenueTypes(1))->showOnPreview()->hideFromIndex(),
                Textarea::make('Details', 'place_details')->alwaysShow()->showOnPreview(),
                Text::make('Details')
                    ->onlyOnIndex()
                    ->displayUsing(fn () => "<div style=\"width: 300px; text-wrap:auto;\">{$this->resource->place_details}</div>")
                    ->asHtml(),
            ]),

            DateTime::make('Created', 'created_at')->hideWhenCreating()->hideWhenUpdating()->showOnPreview(),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            ConvertRecommendationToEatery::make()
                ->showInline()
                ->withoutConfirmation()
                ->sole()
                ->canRun(fn ($request, EateryRecommendation $recommendation) => $recommendation->completed === false && $recommendation->ignored === false),

            CompleteReportOrRecommendation::make()
                ->showInline()
                ->withoutConfirmation()
                ->canRun(fn ($request, EateryRecommendation $recommendation) => $recommendation->completed === false && $recommendation->ignored === false),

            IgnoreReportOrRecommendation::make()
                ->showInline()
                ->withoutConfirmation()
                ->canRun(fn ($request, EateryRecommendation $recommendation) => $recommendation->completed === false && $recommendation->ignored === false),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('email', '!=', 'alisondwheatley@gmail.com')
            ->reorder()
            ->orderByRaw('(completed = 1 or ignored = 1) asc')
            ->orderByDesc('created_at');
    }

    protected function getVenueTypes($typeId = null): array
    {
        return EateryVenueType::query()
            ->when($typeId, fn (Builder $query) => $query->where('type_id', $typeId))
            ->get()
            ->mapWithKeys(fn (EateryVenueType $venueType) => [$venueType->id => $venueType->venue_type])
            ->toArray();
    }
}
