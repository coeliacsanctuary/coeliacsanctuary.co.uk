<?php

declare(strict_types=1);

namespace App\Support\EatingOut\SuggestEdits\Fields;

use App\Models\EatingOut\Eatery;
use RuntimeException;

class InfoField extends EditableField
{
    public function getCurrentValue(Eatery $eatery): ?string
    {
        return '';
    }

    public function commitSuggestedValue(Eatery $eatery): void
    {
        throw new RuntimeException('Can\'t commit info field');
    }
}
