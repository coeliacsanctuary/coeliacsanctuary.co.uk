<?php

declare(strict_types=1);

namespace App\Ai\Tools\PrepareRecommendedEatery;

use App\Models\EatingOut\EateryCuisine;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class EateryCuisineList implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Return a list of eatery cuisine types';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $list = EateryCuisine::query()
            ->orderBy('cuisine')
            ->pluck('cuisine');

        return (string) json_encode($list);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
