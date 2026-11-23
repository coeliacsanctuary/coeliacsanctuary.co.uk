<?php

declare(strict_types=1);

namespace Jpeters8889\EateryOpeningTimes;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryOpeningTimes as EateryOpeningTimesModel;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\SupportsDependentFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class EateryOpeningTimes extends Field
{
    use SupportsDependentFields;

    /**
     * @var string
     */
    public $component = 'eatery-opening-times';

    public $deferrable = true;

    public $showOnIndex = false;

    public $showOnDetail = false;

    public $showOnCreation = true;

    public $showOnUpdate = true;

    public $nullable = true;

    public $fullWidth = true;

    /** @param  Eatery  $resource */
    public function resolveAttribute($resource, $attribute = null): mixed
    {
        $openingTimes = $resource->openingTimes;

        if ( ! $openingTimes) {
            return null;
        }

        return collect(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
            ->mapWithKeys(fn ($day) => [$day => $this->getOpeningTimesForDay($openingTimes, $day)])
            ->jsonSerialize();
    }

    public function getOpeningTimesForDay(EateryOpeningTimesModel $openingTimes, string $day): ?array
    {
        if ( ! $openingTimes->{$day . '_start'}) {
            return null;
        }

        return [
            $openingTimes->formatTime($day . '_start'),
            $openingTimes->formatTime($day . '_end'),
        ];
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute): void
    {
        /** @var array<string, null | array{0: array{0: int, 1: int}, 1: array{0: int, 1: int}}> $value */
        $value = json_decode($request->string($requestAttribute)->toString(), true);

        if ( ! $value) {
            return;
        }

        /** @var Eatery $model */
        $model->openingTimes()->updateOrCreate([], [
            'monday_start' => $value['monday'] ? "{$value['monday'][0][0]}:{$value['monday'][0][1]}:0" : null,
            'monday_end' => $value['monday'] ? "{$value['monday'][1][0]}:{$value['monday'][1][1]}:0" : null,
            'tuesday_start' => $value['tuesday'] ? "{$value['tuesday'][0][0]}:{$value['tuesday'][0][1]}:0" : null,
            'tuesday_end' => $value['tuesday'] ? "{$value['tuesday'][1][0]}:{$value['tuesday'][1][1]}:0" : null,
            'wednesday_start' => $value['wednesday'] ? "{$value['wednesday'][0][0]}:{$value['wednesday'][0][1]}:0" : null,
            'wednesday_end' => $value['wednesday'] ? "{$value['wednesday'][1][0]}:{$value['wednesday'][1][1]}:0" : null,
            'thursday_start' => $value['thursday'] ? "{$value['thursday'][0][0]}:{$value['thursday'][0][1]}:0" : null,
            'thursday_end' => $value['thursday'] ? "{$value['thursday'][1][0]}:{$value['thursday'][1][1]}:0" : null,
            'friday_start' => $value['friday'] ? "{$value['friday'][0][0]}:{$value['friday'][0][1]}:0" : null,
            'friday_end' => $value['friday'] ? "{$value['friday'][1][0]}:{$value['friday'][1][1]}:0" : null,
            'saturday_start' => $value['saturday'] ? "{$value['saturday'][0][0]}:{$value['saturday'][0][1]}:0" : null,
            'saturday_end' => $value['saturday'] ? "{$value['saturday'][1][0]}:{$value['saturday'][1][1]}:0" : null,
            'sunday_start' => $value['sunday'] ? "{$value['sunday'][0][0]}:{$value['sunday'][0][1]}:0" : null,
            'sunday_end' => $value['sunday'] ? "{$value['sunday'][1][0]}:{$value['sunday'][1][1]}:0" : null,
        ]);
    }
}
