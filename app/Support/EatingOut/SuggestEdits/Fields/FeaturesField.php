<?php

declare(strict_types=1);

namespace App\Support\EatingOut\SuggestEdits\Fields;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use Illuminate\Support\Str;

class FeaturesField extends EditableField
{
    public static function validationRules(): array
    {
        return [
            'value' => ['required', 'array'],
            'value.*' => ['array'],
            'value.*.key' => ['required', 'numeric', 'exists:wheretoeat_features,id'],
            'value.*.label' => ['required', 'string'],
            'value.*.selected' => ['required', 'boolean'],
        ];
    }

    protected function transformForSaving(): string|int|null
    {
        return (string) json_encode($this->value);
    }

    public function getCurrentValue(Eatery $eatery): ?string
    {
        return EateryFeature::query()
            ->orderBy('feature')
            ->get()
            ->mapWithKeys(fn (EateryFeature $feature) => [
                $feature->feature => collect($eatery->features->pluck('id'))->contains($feature->id),
            ])
            ->toJson();
    }

    public function commitSuggestedValue(Eatery $eatery): void
    {
        $eatery->features->each(fn (EateryFeature $feature) => $eatery->features()->detach($feature));

        $values = $this->value;

        if (is_string($values)) {
            $values = json_decode($values, true);
        }

        /** @var array{label: string, selected: bool}[] $values */
        collect($values)
            ->filter(fn (array $value) => $value['selected'])
            ->map(fn (array $value) => EateryFeature::query()->where('feature', Str::headline($value['label']))->firstOrFail()->id)
            ->each(fn ($id) => $eatery->features()->attach($id));

        $eatery->touch();
    }
}
