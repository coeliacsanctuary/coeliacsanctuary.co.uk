<?php

declare(strict_types=1);

namespace App\Ai\Tools\PrepareRecommendedEatery;

use App\Models\EatingOut\EateryTown;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchTown implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search for a town in the given county';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $search = EateryTown::query()
            ->withoutGlobalScopes()
            ->whereRelation('county', 'county', $request->string('county'))
            ->whereLike('town', "%{$request->string('town')}%")
            ->orderBy('town')
            ->pluck('town');

        return (string) json_encode($search);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'county' => $schema->string()->required(),
            'town' => $schema->string()->required(),
        ];
    }
}
