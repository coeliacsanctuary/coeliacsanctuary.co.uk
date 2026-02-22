<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Models\EatingOut\EateryRecommendation;
use App\Notifications\EatingOut\EateryRecommendationAddedSmallBusinessNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

/**
 * @codeCoverageIgnore
 */
class IgnoreAndSendAddedToSmallBusinessBlog extends Action
{
    public $name = 'Ignore and send added to small business blog email';

    public $withoutActionEvents = true;

    public function handle(ActionFields $fields, Collection $models): void
    {
        $models->each(function (EateryRecommendation $model): void {
            (new AnonymousNotifiable())
                ->route('mail', $model->email)
                ->notify(new EateryRecommendationAddedSmallBusinessNotification($model));

            $model->update(['ignored' => true]);
        });
    }
}
