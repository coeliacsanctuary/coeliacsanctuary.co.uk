<?php

declare(strict_types=1);

namespace App\Ai\Tools\PrepareRecommendedEatery;

use App\Models\EatingOut\Eatery;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class EateryInfoExamples implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Return a random selection of 10 eatery info fields written in the last 6 months, use this to get an example of the writing style.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $info = Eatery::query()
            ->inRandomOrder()
            ->where('country_id', '>', 1)
            ->where('created_at', '>=', now()->subMonths(6))
            ->take(10)
            ->get()
            ->pluck('info');

        return json_encode($info);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
