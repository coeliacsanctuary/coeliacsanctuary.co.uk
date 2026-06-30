<?php

declare(strict_types=1);

namespace App\Ai\Tools\PrepareRecommendedEatery;

use App\Models\EatingOut\EateryVenueType;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class EateryVenueTypeList implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Return a list of eatery venue types';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $list = EateryVenueType::query()
            ->orderBy('venue_type')
            ->get()
            ->pluck('venue_type');

        return json_encode($list);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
