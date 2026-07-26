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
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List the most popular eateries, by rating, in our database, for a given county.';
    }

    /**
     * Execute the tool.
     */
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
            ->map(fn (Eatery $eatery) => [
                'name' => $eatery->name,
                'town' => $eatery->town?->town,
                'address' => $eatery->address,
                'average_rating' => $eatery->average_rating,
                'total_reviews' => $eatery->reviews->count(),
                'link' => $eatery->absoluteLink(),
            ]);

        return json_encode($places);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'county' => $schema->string()->required(),
        ];
    }
}
