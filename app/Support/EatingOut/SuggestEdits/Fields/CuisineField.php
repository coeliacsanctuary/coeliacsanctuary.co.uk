<?php

declare(strict_types=1);

namespace App\Support\EatingOut\SuggestEdits\Fields;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCuisine;

class CuisineField extends EditableField
{
    public static function validationRules(): array
    {
        return ['required', 'numeric', 'exists:wheretoeat_cuisines,id'];
    }

    public function getCurrentValue(Eatery $eatery): ?string
    {
        return $eatery->cuisine?->cuisine;
    }

    public function getSuggestedValue(): string|int|null
    {
        /** @var EateryCuisine $cuisine */
        $cuisine = EateryCuisine::query()->findOrFail($this->value);

        return $cuisine->cuisine;
    }

    public function commitSuggestedValue(Eatery $eatery): void
    {
        $eatery->update(['cuisine_id' => $this->value]);
    }
}
