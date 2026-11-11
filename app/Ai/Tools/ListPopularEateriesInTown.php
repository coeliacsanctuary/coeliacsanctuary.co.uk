<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryTown;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListPopularEateriesInTown implements Tool
{
    public function description(): Stringable|string
    {
        return 'List the most popular eateries, by rating, in our database, for a given town or London borough.';
    }

    public function handle(Request $request): Stringable|string
    {
        $places = EateryTown::query()
            ->where('town', $request->string('town')->toString())
            ->firstOrFail()
            ->liveEateries()
            ->where('closed_down', false)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->with(['area', 'reviews'])
            ->orderBy('reviews_avg_rating', 'desc')
            ->orderBy('reviews_count', 'desc')
            ->take(5)
            ->get()
            ->map(fn (Eatery $eatery): array => [
                'name' => $eatery->name,
                'london_area' => $eatery->area?->area,
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
            'town' => $schema->string()->required(),
        ];
    }
}
