<?php

declare(strict_types=1);

namespace App\Support\EatingOut\SuggestEdits\Fields;

use App\Models\EatingOut\Eatery;

class PhoneField extends EditableField
{
    public function getCurrentValue(Eatery $eatery): ?string
    {
        return $eatery->phone;
    }

    public function commitSuggestedValue(Eatery $eatery): void
    {
        $eatery->update(['phone' => $this->value]);
    }
}
