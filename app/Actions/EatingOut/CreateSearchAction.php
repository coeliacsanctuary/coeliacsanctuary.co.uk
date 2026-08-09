<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\EatingOut\EaterySearchTerm;

class CreateSearchAction
{
    public function handle(string $term, int $range, bool $fromUserLocation = false): EaterySearchTerm
    {
        $searchTerm = EaterySearchTerm::query()->firstOrCreate([
            'term' => $term,
            'range' => $range,
            'from_user_location' => $fromUserLocation,
        ]);

        $searchTerm->logSearch();

        return $searchTerm;
    }
}
