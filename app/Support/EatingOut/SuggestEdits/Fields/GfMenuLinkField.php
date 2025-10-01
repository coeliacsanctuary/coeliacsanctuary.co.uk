<?php

declare(strict_types=1);

namespace App\Support\EatingOut\SuggestEdits\Fields;

use App\Models\EatingOut\Eatery;

class GfMenuLinkField extends EditableField
{
    public static function validationRules(): array
    {
        return ['required', 'url'];
    }

    public function getCurrentValue(Eatery $eatery): ?string
    {
        return $eatery->gf_menu_link;
    }

    public function commitSuggestedValue(Eatery $eatery): void
    {
        $eatery->update(['gf_menu_link' => $this->value]);
    }
}
