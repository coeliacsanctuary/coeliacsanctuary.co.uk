<?php

declare(strict_types=1);

namespace App\Ai\Tools\PrepareRecommendedEatery;

use App\Models\EatingOut\EateryArea;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchArea implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search for an area in the given london borough';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $search = EateryArea::query()
            ->withoutGlobalScopes()
            ->whereRelation('town', 'town', $request->string('town'))
            ->whereLike('area', "%{$request->string('area')}%")
            ->orderBy('area')
            ->get()
            ->pluck('area');

        return json_encode($search);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'borough' => $schema->string()->required(),
            'area' => $schema->string()->required(),
        ];
    }
}
