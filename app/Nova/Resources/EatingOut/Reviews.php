<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use App\Nova\Actions\EatingOut\ApproveReview;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/**
 * @codeCoverageIgnore
 */
class Reviews extends Resource
{
    public static $model = EateryReview::class;

    public static $title = 'name';

    public static $search = [
        'id',
    ];

    public static $clickAction = 'preview';

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->hide(),

            BelongsTo::make('eatery', resource: Eateries::class)
                ->displayUsing(fn (Eateries $eatery) => $eatery->resource->load(['area', 'town', 'county', 'country'])->full_name)
                ->exceptOnForms(),

            new Panel('User', [
                Text::make('Name')->hideFromIndex()->showOnPreview(),

                Email::make('Email')->hideFromIndex()->showOnPreview(),

                Text::make('Ip')->withMeta(['disabled' => true])->hideFromIndex(),

                Select::make('Method')
                    ->displayUsingLabels()
                    ->options([
                        'website' => 'Website',
                        'app' => 'App',
                    ])
                    ->filterable()
                    ->withMeta(['disabled' => true]),
            ]),

            new Panel('Ratings', [
                Number::make('Rating')
                    ->rules(['required'])
                    ->min(1)
                    ->max(5)
                    ->showOnPreview(),

                Number::make('How Expensive')
                    ->min(1)
                    ->max(5)
                    ->showOnPreview(),

                Select::make('Food Rating')->displayUsingLabels()->options([
                    'poor' => 'Poor',
                    'good' => 'Good',
                    'excellent' => 'Excellent',
                ])->showOnPreview(),

                Select::make('Service Rating')->displayUsingLabels()->options([
                    'poor' => 'Poor',
                    'good' => 'Good',
                    'excellent' => 'Excellent',
                ])->showOnPreview(),
            ]),

            ...($this->resource->eatery?->county_id === 1 ? $this->getBranchPanel() : [Text::make('Branch', fn() => '-')]),

            new Panel('Review', [
                Textarea::make('Review')->showOnPreview(),

                Boolean::make('Approved')->showOnPreview()->filterable(),
            ]),

            Text::make('Images', 'images_count')->onlyOnIndex(),

            DateTime::make('created_at')->exceptOnForms(),

            HasMany::make('Images', 'images', ReviewImage::class),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            ApproveReview::make()->showInline()->canRun(fn ($request, EateryReview $review) => $review->approved === false),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->withoutGlobalScopes()->with(['eatery'])->withCount(['images']);
    }

    protected function getBranchPanel(): array
    {
        return [new Panel('Branch', [
            Select::make('Branch', 'nationwide_branch_id')
                ->displayUsingLabels()
                ->searchable()
                ->options(
                    $this
                        ->resource
                        ->eatery
                        ->nationwideBranches()
                        ->with(['area', 'town', 'county', 'country'])
                        ->chaperone('eatery')
                        ->get()
                        ->mapWithKeys(fn (NationwideBranch $nationwideBranch) => [$nationwideBranch->id => $nationwideBranch->full_name])
                        ->sort()
                ),

            Text::make('User Inputted Branch Name', 'branch_name')->hideFromIndex()->showOnPreview(),
        ])];
    }
}
