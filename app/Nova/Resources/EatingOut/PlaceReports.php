<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EateryReport;
use App\Models\EatingOut\NationwideBranch;
use App\Nova\Actions\EatingOut\CompleteReportOrRecommendation;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class PlaceReports extends Resource
{
    public static $model = EateryReport::class;

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

    public function fields(Request $request): array
    {
        return [
            ID::make()->hide(),

            BelongsTo::make('Eatery', resource: Eateries::class)
                ->hideFromIndex(fn ($foo, EateryReport $report) => $report->eatery->county_id === 1)
                ->hideFromDetail(fn ($foo, EateryReport $report) => $report->eatery->county_id === 1)
                ->displayUsing(fn (Eateries $eatery) => $eatery->resource->load(['area', 'town' => fn (Relation $builder) => $builder->withoutGlobalScopes(), 'county', 'country'])->full_name),

            BelongsTo::make('Eatery', resource: NationwideEateries::class)
                ->hideFromIndex(fn ($foo, EateryReport $report) => $report->eatery->county_id !== 1)
                ->hideFromDetail(fn ($foo, EateryReport $report) => $report->eatery->county_id !== 1)
                ->displayUsing(fn (NationwideEateries $eatery) => $eatery->resource->load(['area', 'town' => fn (Relation $builder) => $builder->withoutGlobalScopes(), 'county', 'country'])->full_name),

            Select::make('Branch', 'branch_id')
                ->displayUsingLabels()
                ->searchable()
                ->options(
                    $this
                        ->resource
                        ->eatery
                        ?->nationwideBranches()
                        ->with(['town', 'county', 'country'])
                        ->chaperone('eatery')
                        ->get()
                        ->mapWithKeys(fn (NationwideBranch $nationwideBranch) => [$nationwideBranch->id => $nationwideBranch->full_name])
                        ->sort() ?? []
                ),

            Text::make('User Inputted Branch Name', 'branch_name')->hideFromIndex()->showOnPreview(),

            Text::make('Details')
                ->showOnPreview()
                ->displayUsing(fn (string $details) => Str::wordWrap($details, 100, '<br />'))
                ->asHtml(),

            HasOne::make('Eatery', resource: Eateries::class)
                ->hideFromIndex(fn ($foo, EateryReport $report) => $report->eatery->county_id === 1)
                ->hideFromDetail(fn ($foo, EateryReport $report) => $report->eatery->county_id === 1),

            HasOne::make('Eatery', 'eatery', resource: NationwideEateries::class)
                ->hideFromIndex(fn ($foo, EateryReport $report) => $report->eatery->county_id !== 1)
                ->hideFromDetail(fn ($foo, EateryReport $report) => $report->eatery->county_id !== 1),

            HasOne::make('Branch', 'branch', resource: NationwideBranches::class)
                ->showOnDetail(fn ($foo, EateryReport $report) => $report->branch_id !== null),

            Boolean::make('Completed')->showOnPreview(),

            DateTime::make('Created', 'created_at')->showOnPreview(),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            CompleteReportOrRecommendation::make()
                ->showInline()
                ->withoutConfirmation()
                ->canRun(fn ($request, EateryReport $report) => $report->completed === false),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->withoutGlobalScopes()
            ->with([
                'eatery' => fn (Relation $builder) => $builder->withoutGlobalScopes()->with(['town', 'county', 'country']),
                'branch' => fn (Relation $builder) => $builder->withoutGlobalScopes()->with(['town', 'county', 'country']),
            ])
            ->reorder('completed')
            ->orderByDesc('created_at');
    }
}
