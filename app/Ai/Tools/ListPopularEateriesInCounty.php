<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListPopularEateriesInCounty implements Tool
{
    public function description(): Stringable|string
    {
        return 'List the most popular eateries, by rating, in our database, for a given county.';
    }

    public function handle(Request $request): Stringable|string
    {
        $places = EateryCounty::query()
            ->where('county', $request->string('county')->toString())
            ->firstOrFail()
            ->eateries()
            ->where('live', true)
            ->where('closed_down', false)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->with(['town', 'reviews'])
            ->orderBy('reviews_avg_rating', 'desc')
            ->orderBy('reviews_count', 'desc')
            ->take(5)
            ->get()
            ->map(fn (Eatery $eatery): array => [
                'name' => $eatery->name,
                'town' => $eatery->town?->town,
                'address' => $eatery->address,
                'average_rating' => $eatery->average_rating,
                'total_reviews' => $eatery->reviews->count(),
                'link' => $eatery->absoluteLink(),
            ]);

        return (string) json_encode($places);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'county' => $schema->string()->required(),
        ];
    }
}
