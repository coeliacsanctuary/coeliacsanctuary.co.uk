<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Models\EatingOut\EateryMagicRouteRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class RegenerateMagicRouteBody extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * @param Collection<int, EateryMagicRouteRecord> $models
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        $record = $models->first();

        $agent = $record->resolver_type->agent([$record]);

        $record->update([
            'body' => json_decode($agent->prompt('Generate the content')->text, true),
        ]);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
