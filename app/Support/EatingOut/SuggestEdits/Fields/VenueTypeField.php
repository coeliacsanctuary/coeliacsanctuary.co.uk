<?php

declare(strict_types=1);

namespace App\Support\EatingOut\SuggestEdits\Fields;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryVenueType;

class VenueTypeField extends EditableField
{
    public static function validationRules(): array
    {
        return ['required', 'numeric', 'exists:wheretoeat_venue_types,id'];
    }

    public function getCurrentValue(Eatery $eatery): ?string
    {
        return $eatery->venueType?->venue_type;
    }

    public function getSuggestedValue(): string|int|null
    {
        /** @var EateryVenueType $venueType */
        $venueType = EateryVenueType::query()->findOrFail($this->value);

        return $venueType->venue_type;
    }

    public function commitSuggestedValue(Eatery $eatery): void
    {
        $eatery->update(['venue_type_id' => $this->value]);
    }
}
