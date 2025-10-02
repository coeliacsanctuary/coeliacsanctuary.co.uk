<?php

declare(strict_types=1);

namespace App\Support\EatingOut\SuggestEdits\Fields;

use App\Models\EatingOut\Eatery;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OpeningTimesField extends EditableField
{
    public static array $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public static function validationRules(): array
    {
        return [
            'value' => ['required', 'array', 'size:7'],
            'value.*' => ['array'],
            'value.*.key' => ['required', Rule::in(static::$days)],
            'value.*.label' => ['required', Rule::in(array_map(fn ($day) => ucfirst($day), static::$days))],
            'value.*.start' => ['required', 'array', 'size:2'],
            'value.*.start.0' => ['present', 'nullable', 'numeric', 'min:0', 'max:23'],
            'value.*.start.1' => ['present', 'nullable', 'numeric', Rule::in([0, 15, 30, 45])],
            'value.*.end' => ['required', 'array', 'size:2'],
            'value.*.end.0' => ['present', 'nullable', 'numeric', 'min:0', 'max:23', 'after:value.*.start.0'],
            'value.*.end.1' => ['present', 'nullable', 'numeric', Rule::in([0, 15, 30, 45])],
        ];
    }

    protected function transformForSaving(): string|int|null
    {
        return (string) json_encode($this->value);
    }

    public function getCurrentValue(Eatery $eatery): ?string
    {
        $openingTimes = $eatery->openingTimes;

        if ($openingTimes === null) {
            return null;
        }

        return collect(self::$days)
            ->map(fn ($day) => [
                'key' => $day,
                'label' => Str::title($day),
                'closed' => $openingTimes->{$day . '_start'} === null,
                'start' => $openingTimes->formatTime($day . '_start'),
                'end' => $openingTimes->formatTime($day . '_end'),
            ])
            ->toJson();
    }

    public function commitSuggestedValue(Eatery $eatery): void
    {
        $value = $this->value;

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        $makeTime = fn (array $time) => $time[0] === null ? null : Str::padLeft((string) $time[0], 2, '0') . ':' . Str::padLeft((string) $time[1], 2, '0') . ':00';

        /** @var array{key: 'monday' | 'tuesday' | 'wednesday' | 'thursday' | 'friday' | 'saturday' | 'sunday', start: array{int<0,23>, 0|15|30|45}, end: array{int<0,23>, 0|15|30|45}}[] $value */
        $data = collect($value)->mapWithKeys(fn (array $times) => [
            "{$times['key']}_start" => $makeTime($times['start']),
            "{$times['key']}_end" => $makeTime($times['end']),
        ])->toArray();

        $eatery->openingTimes()->updateOrCreate([], $data);

        $eatery->touch();
    }
}
