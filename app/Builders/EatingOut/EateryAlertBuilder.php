<?php

declare(strict_types=1);

namespace App\Builders\EatingOut;

use App\Models\EatingOut\EateryAlert;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<EateryAlert> */
class EateryAlertBuilder extends Builder
{
    public function pending(): static
    {
        return $this->where('completed', false)->where('ignored', false);
    }
}
