<?php

declare(strict_types=1);

namespace App\Concerns\EatingOut;

trait QueriesNationwideEateries
{
    public function nationwide(): static
    {
        return $this->where('county_id', 1);
    }

    public function notNationwide(): static
    {
        return $this->where('county_id', '!=', 1);
    }
}
