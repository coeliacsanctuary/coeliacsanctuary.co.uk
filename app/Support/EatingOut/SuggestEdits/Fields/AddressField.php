<?php

declare(strict_types=1);

namespace App\Support\EatingOut\SuggestEdits\Fields;

use App\Models\EatingOut\Eatery;

class AddressField extends EditableField
{
    public static function validationRules(): array
    {
        return ['required', 'string'];
    }

    public function getCurrentValue(Eatery $eatery): ?string
    {
        return $eatery->address;
    }

    public function commitSuggestedValue(Eatery $eatery): void
    {
        $eatery->update(['address' => $this->value]);
    }
}
