<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Models\EateryAiDescription;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ApproveAiDescription extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * @param  Collection<int, EateryAiDescription>  $models
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        $models->loadMissing('eatery');

        $models->each(function (EateryAiDescription $ai): void {
            $ai->eatery->update(['info' => $ai->description]);

            $ai->delete();
        });
    }
}
