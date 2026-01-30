<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EateryAlert;
use App\Nova\Actions\EatingOut\CompleteReportOrRecommendation;
use App\Nova\Actions\EatingOut\IgnoreReportOrRecommendation;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class EateryAlerts extends Resource
{
    public static $model = EateryAlert::class;

    public static $clickAction = 'view';

    public static $tableStyle = 'tight';

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

            BelongsTo::make('Eatery', resource: Eateries::class)
                ->displayUsing(fn (Eateries $eatery) => $eatery->resource->load(['area', 'town' => fn (Relation $builder) => $builder->withoutGlobalScopes(), 'county', 'country'])->full_name),

            Text::make('Check Type', 'type'),

            Text::make('Details')
                ->showOnPreview()
                ->displayUsing(fn (string $details) => Str::wordWrap($details, 100, '<br />'))
                ->asHtml(),

            Boolean::make('Completed')
                ->filterable()
                ->showOnPreview(),

            Boolean::make('Ignored')
                ->filterable()
                ->showOnPreview(),

            DateTime::make('Created', 'created_at')->showOnPreview(),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            CompleteReportOrRecommendation::make()
                ->showInline()
                ->withoutConfirmation()
                ->canRun(fn ($request, EateryAlert $report) => $report->completed === false && $report->ignored === false),

            IgnoreReportOrRecommendation::make()
                ->showInline()
                ->withoutConfirmation()
                ->canRun(fn ($request, EateryAlert $report) => $report->completed === false && $report->ignored === false),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->withoutGlobalScopes()
            ->with([
                'eatery' => fn (Relation $builder) => $builder->withoutGlobalScopes()->with(['area', 'town', 'county', 'country']),
            ])
            ->reorder()
            ->orderByRaw('(completed = 1 or ignored = 1) asc')
            ->orderByDesc('created_at');
    }
}
