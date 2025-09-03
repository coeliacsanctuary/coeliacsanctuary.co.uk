<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Models\EatingOut\EateryReview;
use App\Notifications\EatingOut\EateryReviewApprovedNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class ApproveReview extends Action
{
    public $name = 'Approve';

    public $withoutActionEvents = true;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(function (EateryReview $review): void {
            if ($review->approved) {
                return;
            }

            $review->update(['approved' => true]);

            $review->eatery->sealiacOverview?->update([
                'invalidated' => true,
            ]);

            $review->eatery->touch();

            (new AnonymousNotifiable())
                ->route('mail', $review->email)
                ->notify(new EateryReviewApprovedNotification($review));
        });
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
