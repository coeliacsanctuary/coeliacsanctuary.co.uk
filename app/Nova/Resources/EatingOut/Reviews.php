<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use App\Notifications\EatingOut\EateryReviewApprovedNotification;
use App\Nova\Actions\EatingOut\ApproveReview;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
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

    public static $clickAction = 'detail';

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return true;
    }

    public function fields(Request $request): array
    {
        $eateryRelations = [
            'area' => fn (Relation $query) => $query->withoutGlobalScopes(),
            'town' => fn (Relation $query) => $query->withoutGlobalScopes(),
            'county' => fn (Relation $query) => $query->withoutGlobalScopes(),
            'country' => fn (Relation $query) => $query->withoutGlobalScopes(),
        ];

        return [
            ID::make()->hide(),

            Stack::make('Eatery', [
                BelongsTo::make('eatery', resource: Eateries::class)->displayUsing(fn ($eatery) => $eatery->resource->load($eateryRelations)->full_name),
                ...($this->resource->eatery?->county_id === 1 ? [BelongsTo::make('branch', resource: NationwideBranches::class)->displayUsing(fn ($branch) => "Branch: {$branch->resource->load($eateryRelations)->full_name}")] : [Text::make('Branch', fn () => '')]),
            ])->onlyOnIndex(),

            BelongsTo::make('eatery', resource: Eateries::class)
                ->displayUsing(fn (Eateries $eatery) => $eatery->resource->load($eateryRelations)->full_name)
                ->exceptOnForms()
                ->hideFromIndex(),

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

            Stack::make('Ratings', [
                Line::make('Rating')->displayUsing(fn ($rating) => "Rating: {$rating}"),
                Line::make('How Expensive')->displayUsing(fn ($rating) => "How Expensive: {$rating}"),
                Line::make('Food Rating')->displayUsing(fn ($rating) => "Food Rating: {$rating}"),
                Line::make('Service Rating')->displayUsing(fn ($rating) => "Service Rating: {$rating}"),
            ])->onlyOnIndex(),

            Text::make('Review')
                ->onlyOnIndex()
                ->displayUsing(fn () => "<div style=\"width: 300px; text-wrap:auto;\">{$this->resource->review}</div>")
                ->asHtml(),

            new Panel('Ratings', [
                Number::make('Rating')
                    ->hideFromIndex()
                    ->rules(['required'])
                    ->min(1)
                    ->max(5)
                    ->showOnPreview(),

                Number::make('How Expensive')
                    ->hideFromIndex()
                    ->min(1)
                    ->max(5)
                    ->showOnPreview(),

                Select::make('Food Rating')
                    ->hideFromIndex()
                    ->displayUsingLabels()
                    ->options([
                        'poor' => 'Poor',
                        'good' => 'Good',
                        'excellent' => 'Excellent',
                    ])
                    ->showOnPreview(),

                Select::make('Service Rating')
                    ->hideFromIndex()
                    ->displayUsingLabels()
                    ->options([
                        'poor' => 'Poor',
                        'good' => 'Good',
                        'excellent' => 'Excellent',
                    ])
                    ->showOnPreview(),
            ]),

            ...($this->resource->eatery?->county_id === 1 ? $this->getBranchPanel() : [Text::make('Branch', fn () => '-')->hideFromIndex()]),

            new Panel('Review', [
                Textarea::make('Review')->showOnPreview()->alwaysShow(),

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
        return $query->withoutGlobalScopes()->with(['eatery' => fn (Relation $query) => $query->withoutGlobalScopes()])->withCount(['images']);
    }

    protected function getBranchPanel(): array
    {
        return [new Panel('Branch', [
            Select::make('Branch', 'nationwide_branch_id')
                ->displayUsingLabels()
                ->searchable()
                ->hideFromIndex()
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

    public static function afterUpdate(NovaRequest $request, Model $model): void
    {
        /** @var EateryReview $model */
        if ($model->approved && (bool) $model->getPrevious()['approved'] === false) {
            (new AnonymousNotifiable())
                ->route('mail', $model->email)
                ->notify(new EateryReviewApprovedNotification($model));
        }
    }
}
