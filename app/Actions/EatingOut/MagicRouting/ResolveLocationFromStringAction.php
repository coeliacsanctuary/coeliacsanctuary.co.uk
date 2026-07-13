<?php

declare(strict_types=1);

namespace App\Actions\EatingOut\MagicRouting;

use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;

class ResolveLocationFromStringAction
{
    public function handle(string $location): EateryCounty|EateryTown|EateryArea|null
    {
        /** @var class-string<EateryCounty|EateryTown|EateryArea>[] $checks */
        $checks = [
            EateryCounty::class,
            EateryTown::class,
            EateryArea::class,
        ];

        foreach ($checks as $check) {
            $exists = $check::query()
                ->where('slug', $location)
                ->first();

            if ($exists) {
                return $exists;
            }
        }

        return null;
    }
}
