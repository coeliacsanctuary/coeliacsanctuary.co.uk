<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryRecommendation;
use App\Notifications\EatingOut\EateryRecommendationAlreadyExistsNotification;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Support\Fluent;
use ZiffMedia\NovaSelectPlus\SelectPlus;

/**
 * @codeCoverageIgnore
 */
class IgnoreAndSendPlaceAlreadyExists extends Action
{
    public $name = 'Ignore and send place already exists email';

    public $withoutActionEvents = true;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(function (EateryRecommendation $model) use ($fields): void {
            $eateryInfo = json_decode($fields->get('eatery_id'), true);
            [$eateryId, $branchId] = explode(':', $eateryInfo[0]['value']);

            $eatery = Eatery::query()->find($eateryId);
            $branch = null;

            if ($branchId) {
                $branch = $eatery->nationwideBranches()->find($branchId);
            }

            (new AnonymousNotifiable())
                ->route('mail', $model->email)
                ->notify(new EateryRecommendationAlreadyExistsNotification($model, $eatery, $branch));

            $model->update(['ignored' => true]);
        });
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            SelectPlus::make('eatery_id')
                ->fillUsing(function (Request $request, Fluent $fluent, $attribute, $requestAttribute): void {
                    $fluent->set($attribute, $request->get($requestAttribute));
                })
                ->options([])
                ->ajaxSearchable(fn ($search) => []),
        ];
    }
}
