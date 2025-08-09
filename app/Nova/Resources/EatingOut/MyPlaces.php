<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryVenueType;
use App\Nova\Actions\EatingOut\CompleteReportOrRecommendation;
use App\Nova\Actions\EatingOut\ConvertRecommendationToEatery;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
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
class MyPlaces extends Resource
{
    public static $model = EateryRecommendation::class;

    public static $clickAction = 'preview';

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public static function createButtonLabel()
    {
        return 'Create Place Recommendation';
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->hide(),

            Panel::make('Eatery', [
                Text::make('Name', 'place_name')->rules(['required'])->showOnPreview(),
                Text::make('Location', 'place_location')->rules(['required'])->showOnPreview(),
                URL::make('URL', 'place_web_address')->showOnPreview(),
                Select::make('Venue Type', 'place_venue_type_id')->displayUsingLabels()->options($this->getVenueTypes(1))->showOnPreview(),
                Textarea::make('Details', 'place_details')->alwaysShow()->showOnPreview(),
            ]),

            Boolean::make('Completed')->hideWhenCreating()->hideWhenUpdating()->showOnPreview(),

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
                ->canRun(fn ($request, EateryRecommendation $recommendation) => $recommendation->completed === false),

            CompleteReportOrRecommendation::make()
                ->showInline()
                ->withoutConfirmation()
                ->canRun(fn ($request, EateryRecommendation $report) => $report->completed === false),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('email', 'alisondwheatley@gmail.com')->reorder('completed')->orderBy('created_at');
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
